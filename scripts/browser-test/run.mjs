// Cross-browser smoke test for a SocietyPress site.
//
// WHY it exists: front-end bugs land on browsers and screen sizes nobody on
// the project happens to use — a phone header that hid Log Out went unseen
// until a volunteer reported it. This loads the public pages, signed out and
// signed in, in every engine members actually use, and checks the things a
// volunteer would notice: the page loads, nothing scrolls sideways, no script
// errors, and Log In / Log Out can be reached at every size.
//
// Usage:  node run.mjs [site-label ...]
// Config: ../browser-test.local.json (gitignored — site URLs and test logins).
// Output: ~/SocietyPress-Private/browser-tests/<timestamp>/index.html

import { chromium, firefox, webkit, devices } from 'playwright';
import { existsSync, mkdirSync, readFileSync, writeFileSync, readdirSync, rmSync } from 'node:fs';
import { homedir } from 'node:os';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { execFileSync } from 'node:child_process';

const here   = dirname( fileURLToPath( import.meta.url ) );
const config = JSON.parse( readFileSync( join( here, '..', 'browser-test.local.json' ), 'utf8' ) );
const only   = process.argv.slice( 2 );
const sites  = config.sites.filter( s => ! only.length || only.includes( s.label ) );

const root  = join( homedir(), 'SocietyPress-Private', 'browser-tests' );
const stamp = new Date().toISOString().replace( /[:T]/g, '-' ).slice( 0, 16 );
const out   = join( root, stamp );
mkdirSync( out, { recursive: true } );

const DESKTOP = { viewport: { width: 1366, height: 900 } };
const TABLET  = { viewport: { width: 820, height: 1180 }, hasTouch: true };

// WHY the branded browsers as well as the three engines: Edge and Opera are
// Chromium underneath, but ship their own defaults (fonts, tracking blockers)
// that have broken sites before. Each is skipped, not failed, if not installed.
const OPERA = '/Applications/Opera.app/Contents/MacOS/Opera';
const browsers = [
    { name: 'Chrome',  type: chromium, launch: { channel: 'chrome' } },
    { name: 'Edge',    type: chromium, launch: { channel: 'msedge' } },
    { name: 'Opera',   type: chromium, launch: { executablePath: OPERA }, needs: OPERA },
    { name: 'Firefox', type: firefox,  launch: {} },
    // Playwright cannot drive the Safari app itself; its WebKit build is the
    // same engine, and the only way to emulate an iPhone. Real Safari is
    // covered separately below via safaridriver.
    { name: 'WebKit',  type: webkit,   launch: {} },
];
const sizes = [ [ 'desktop', DESKTOP ], [ 'tablet', TABLET ] ];
const phones = [
    { name: 'iPhone',  type: webkit,   device: devices[ 'iPhone 15 Pro' ] },
    { name: 'Android', type: chromium, device: devices[ 'Pixel 7' ] },
];

// Runs inside the page. Kept as one function so real Safari (Selenium) can run
// the identical check.
const pageFacts = () => {
    const visible = el => !! el && el.getClientRects().length > 0 && getComputedStyle( el ).visibility !== 'hidden';
    const toggle  = [ ...document.querySelectorAll( '.sp-hamburger, .ledger-hamburger' ) ].find( visible );
    return {
        overflow: document.documentElement.scrollWidth - window.innerWidth,
        hasToggle: !! toggle,
    };
};

async function accountLinks( page, signedIn ) {
    const want = signedIn ? /log out/i : /log in/i;
    const toggle = page.locator( '.sp-hamburger:visible, .ledger-hamburger:visible' ).first();
    if ( await toggle.count() ) {
        await toggle.click();
        await page.waitForTimeout( 400 );
    } else if ( signedIn ) {
        // The desktop account menu opens on hover; Log Out lives inside it.
        const trigger = page.locator( '.sp-user-trigger:visible' ).first();
        if ( await trigger.count() ) {
            await trigger.hover();
            await page.waitForTimeout( 300 );
        }
    }
    return page.getByRole( 'link', { name: want } ).filter( { visible: true } ).count();
}

async function signIn( page, site, account ) {
    try {
        return await trySignIn( page, site, account );
    } catch ( e ) {
        return false;
    }
}

async function trySignIn( page, site, account ) {
    await page.goto( site.url + '/wp-login.php', { waitUntil: 'domcontentloaded', timeout: 90000 } );
    await page.fill( '#user_login', account.user );
    await page.fill( '#user_pass', account.pass );
    await Promise.all( [ page.waitForLoadState( 'domcontentloaded' ), page.click( '#wp-submit' ) ] );
    await page.waitForTimeout( 500 );
    return ( await page.context().cookies() ).some( c => c.name.startsWith( 'wordpress_logged_in' ) );
}

// The pages worth checking differ per society, so take them from the site's
// own primary menu rather than a hard-coded list.
async function discoverPages( site ) {
    const b = await chromium.launch();
    const p = await b.newPage();
    await p.goto( site.url + '/', { waitUntil: 'domcontentloaded', timeout: 60000 } );
    const hrefs = await p.$$eval( '.main-navigation:not(.sp-user-menu) > ul > li:not(.sp-nav-account) > a, .ledger-navigation > ul > li:not(.sp-nav-account) > a',
        as => as.map( a => a.href ) );
    await b.close();
    const origin = new URL( site.url ).origin;
    const paths = [ ...new Set( hrefs.filter( h => h.startsWith( origin ) ).map( h => new URL( h ).pathname ) ) ];
    return [ '/', ...paths.filter( p => p !== '/' ) ].slice( 0, 7 );
}


// ---- Committee-chair checks -------------------------------------------------
//
// WHY a swap: a chair is whoever a committee record names, one per committee,
// and a live society has no spare committee to lend a test account. Where the
// config gives a chairSwap, the test account takes that committee's chair for
// the minute the checks need and the real chair is put straight back. The
// original is written to disk before the swap, so a run that dies midway is
// undone by the next run before it does anything else.
function remoteWp( sw, args ) {
    return execFileSync( 'ssh', [ sw.ssh, `${ sw.wp } ${ args }` ], { stdio: [ 'ignore', 'pipe', 'ignore' ] } ).toString().trim();
}
function setChair( sw, userId ) {
    remoteWp( sw, `db query "UPDATE \$(wp db prefix)sp_committees SET chair_user_id = ${ Number( userId ) } WHERE id = ${ Number( sw.committee ) }"` );
}
function swapFile( site ) {
    return join( root, `chair-swap-${ site.label }.json` );
}
function restoreLeftoverSwap( site ) {
    if ( ! site.chairSwap || ! existsSync( swapFile( site ) ) ) return;
    const { original } = JSON.parse( readFileSync( swapFile( site ), 'utf8' ) );
    setChair( site.chairSwap, original );
    rmSync( swapFile( site ) );
    console.log( `${ site.label }: restored committee ${ site.chairSwap.committee } chair left over from an interrupted run` );
}
async function withChair( site, fn ) {
    const sw = site.chairSwap;
    if ( ! sw ) return fn();
    const original = remoteWp( sw, `db query "SELECT chair_user_id FROM \$(wp db prefix)sp_committees WHERE id = ${ Number( sw.committee ) }" --skip-column-names` );
    const testId   = remoteWp( sw, `user get ${ sw.testUser } --field=ID` );
    writeFileSync( swapFile( site ), JSON.stringify( { original } ) );
    setChair( sw, testId );
    try {
        return await fn();
    } finally {
        setChair( sw, original );
        rmSync( swapFile( site ) );
    }
}
async function checkChair( site, state ) {
    for ( const [ name, type ] of [ [ 'Chrome', chromium ], [ 'Firefox', firefox ], [ 'WebKit', webkit ] ] ) {
        const b = await type.launch( name === 'Chrome' ? { channel: 'chrome' } : {} );
        try {
            const page = await ( await b.newContext( { ...DESKTOP, storageState: state } ) ).newPage();
            await page.goto( site.url + '/wp-admin/admin.php?page=societypress', { waitUntil: 'load', timeout: 90000 } );
            const ok = await page.locator( 'a[href*="page=sp-chair"]' ).filter( { visible: true } ).count();
            record( { site: site.label, browser: name, size: 'desktop', who: 'chair', path: 'admin sidebar', status: ok ? 'pass' : 'fail',
                notes: ok ? [] : [ 'My Committee missing from sidebar' ], shot: '' } );
        } catch ( e ) {
            record( { site: site.label, browser: name, size: 'desktop', who: 'chair', path: 'admin sidebar', status: 'fail', notes: [ e.message.split( '\n' )[ 0 ] ], shot: '' } );
        } finally {
            await b.close();
        }
    }
}

const results = [];
function record( r ) {
    results.push( r );
    const mark = r.status === 'pass' ? 'ok  ' : r.status === 'skip' ? 'skip' : 'FAIL';
    if ( r.status !== 'pass' ) console.log( `${mark} ${r.site} ${r.browser} ${r.size} ${r.who} ${r.path} — ${r.notes.join( '; ' )}` );
}

async function checkPage( page, site, path, ctxInfo, signedIn ) {
    const notes  = [];
    const errors = [];
    const onErr  = e => errors.push( e.message.split( '\n' )[ 0 ] );
    page.on( 'pageerror', onErr );
    let status = 0;
    try {
        const res = await page.goto( site.url + path, { waitUntil: 'load', timeout: 90000 } );
        status = res ? res.status() : 0;
    } catch ( e ) {
        notes.push( 'did not load: ' + e.message.split( '\n' )[ 0 ] );
    }
    if ( status >= 400 ) notes.push( 'HTTP ' + status );
    await page.waitForTimeout( 800 );
    const shot = `${site.label}-${ctxInfo.browser}-${ctxInfo.size}-${ctxInfo.who}-${path.replace( /\W+/g, '_' ) || 'home'}.png`;
    if ( ! notes.length ) {
        const f = await page.evaluate( pageFacts );
        if ( f.overflow > 1 ) notes.push( `scrolls sideways by ${f.overflow}px` );
        await page.screenshot( { path: join( out, shot ) } );
        if ( path === '/' && ! await accountLinks( page, signedIn ) ) {
            notes.push( signedIn ? 'no visible Log Out' : 'no visible Log In' );
        }
    }
    errors.forEach( e => notes.push( 'script error: ' + e ) );
    page.off( 'pageerror', onErr );
    record( { site: site.label, ...ctxInfo, path, status: notes.length ? 'fail' : 'pass', notes, shot } );
}

async function runContext( type, launch, ctxOpts, site, pages, info, sessions ) {
    const b = await type.launch( launch );
    try {
        // Signed out: every discovered page.
        let ctx = await b.newContext( ctxOpts );
        let page = await ctx.newPage();
        for ( const path of pages ) await checkPage( page, site, path, { ...info, who: 'visitor' }, false );
        await ctx.close();

        // Signed in: the home page as each test account, plus the librarian's
        // admin sidebar — the access-area bug that hid every screen from
        // delegated staff is exactly the kind of thing this should catch.
        for ( const [ who, state ] of Object.entries( sessions ) ) {
            ctx  = await b.newContext( { ...ctxOpts, storageState: state } );
            page = await ctx.newPage();
            await checkPage( page, site, '/', { ...info, who }, true );
            if ( who === 'librarian' && info.size === 'desktop' ) {
                await page.goto( site.url + '/wp-admin/admin.php?page=societypress', { waitUntil: 'load' } );
                // By link, not label: the sidebar groups and renames screens, and a
                // collapsed group hides its labels from text matching.
                // The grouped sidebar opens each group as a flyout outside
                // #adminmenu, so click the group the way a person would and
                // look for a link they could actually see.
                const group = page.locator( '#adminmenu a', { hasText: /^Library$/ } ).first();
                if ( await group.count() ) await group.click();
                await page.waitForTimeout( 500 );
                const ok = await page.locator( 'a[href*="page=sp-library-catalog"]' ).filter( { visible: true } ).count();
                record( { site: site.label, ...info, who, path: 'admin sidebar', status: ok ? 'pass' : 'fail',
                    notes: ok ? [] : [ 'Library Catalog missing from sidebar' ], shot: '' } );
            }
            await ctx.close();
        }
    } finally {
        await b.close();
    }
}

async function runSafari( site, pages ) {
    let driver;
    try {
        const { Builder } = await import( 'selenium-webdriver' );
        driver = await new Builder().forBrowser( 'safari' ).build();
    } catch ( e ) {
        record( { site: site.label, browser: 'Safari', size: 'desktop', who: 'visitor', path: '-', status: 'skip',
            notes: [ 'Safari automation is off — Safari > Develop > Allow Remote Automation' ], shot: '' } );
        return;
    }
    try {
        await driver.manage().window().setRect( { width: 1366, height: 900 } );
        for ( const path of pages ) {
            await driver.get( site.url + path );
            const f = await driver.executeScript( `return (${ pageFacts.toString() })()` );
            const shot = `${site.label}-Safari-desktop-visitor-${path.replace( /\W+/g, '_' ) || 'home'}.png`;
            writeFileSync( join( out, shot ), await driver.takeScreenshot(), 'base64' );
            const notes = f.overflow > 1 ? [ `scrolls sideways by ${f.overflow}px` ] : [];
            record( { site: site.label, browser: 'Safari', size: 'desktop', who: 'visitor', path, status: notes.length ? 'fail' : 'pass', notes, shot } );
        }
    } finally {
        await driver.quit();
    }
}

for ( const site of sites ) {
    restoreLeftoverSwap( site );
    const pages = await discoverPages( site ).catch( () => [ '/' ] );
    console.log( `${site.label}: ${pages.join( ' ' )}` );

    // WHY sign in once and share the session: every browser signing in on its
    // own meant a burst of logins a minute, which a host's brute-force guard
    // reads as an attack and answers by blocking the address. One login per
    // account, reused everywhere, looks like what it is.
    const sessions = {};
    for ( const [ who, account ] of Object.entries( config.accounts ) ) {
        const b = await chromium.launch();
        const ctx = await b.newContext();
        const page = await ctx.newPage();
        if ( await signIn( page, site, account ) ) {
            sessions[ who ] = await ctx.storageState();
        } else {
            record( { site: site.label, browser: '-', size: '-', who, path: 'login', status: 'fail', notes: [ 'could not sign in' ], shot: '' } );
        }
        await b.close();
    }

    const jobs = [];
    for ( const br of browsers ) {
        if ( br.needs && ! existsSync( br.needs ) ) {
            record( { site: site.label, browser: br.name, size: '-', who: '-', path: '-', status: 'skip', notes: [ 'not installed' ], shot: '' } );
            continue;
        }
        for ( const [ size, opts ] of sizes ) {
            jobs.push( () => runContext( br.type, br.launch, opts, site, pages, { browser: br.name, size }, sessions ).catch( e =>
                record( { site: site.label, browser: br.name, size, who: '-', path: '-', status: e.message.includes( 'is not found' ) || e.message.includes( "Executable doesn't exist" ) ? 'skip' : 'fail', notes: [ e.message.split( '\n' )[ 0 ] ], shot: '' } ) ) );
        }
    }
    for ( const ph of phones ) {
        jobs.push( () => runContext( ph.type, {}, ph.device, site, pages, { browser: ph.name, size: 'phone' }, sessions ).catch( e =>
            record( { site: site.label, browser: ph.name, size: 'phone', who: '-', path: '-', status: 'fail', notes: [ e.message.split( '\n' )[ 0 ] ], shot: '' } ) ) );
    }
    // Two at a time: shared hosting slows to a crawl, and starts timing pages
    // out, with a dozen browsers hitting it at once.
    const pool = Array.from( { length: 2 }, async () => { while ( jobs.length ) await jobs.shift()(); } );
    await Promise.all( pool );
    if ( sessions.chair ) await withChair( site, () => checkChair( site, sessions.chair ) );
    await runSafari( site, pages );
}

// A single page to look over: failures first, then every screenshot.
const esc = s => String( s ).replace( /[&<>"]/g, c => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' } )[ c ] );
const rows = results.slice().sort( ( a, b ) => ( a.status === 'fail' ? 0 : 1 ) - ( b.status === 'fail' ? 0 : 1 ) );
writeFileSync( join( out, 'index.html' ), `<!doctype html><meta charset="utf-8"><title>Browser test ${stamp}</title>
<style>body{font:14px system-ui;margin:16px}td{padding:2px 8px;border-bottom:1px solid #ddd}.fail{color:#b00}.skip{color:#888}
.grid{display:flex;flex-wrap:wrap;gap:12px}.grid figure{margin:0;width:220px}.grid img{width:100%;border:1px solid #ccc}figcaption{font-size:11px}</style>
<h1>Browser test ${stamp}</h1><table>${ rows.map( r => `<tr class="${r.status}"><td>${r.status}</td><td>${esc( r.site )}</td><td>${esc( r.browser )}</td><td>${esc( r.size )}</td><td>${esc( r.who )}</td><td>${esc( r.path )}</td><td>${esc( r.notes.join( '; ' ) )}</td></tr>` ).join( '' ) }</table>
<div class="grid">${ results.filter( r => r.shot ).map( r => `<figure><a href="${esc( r.shot )}"><img src="${esc( r.shot )}" loading="lazy"></a><figcaption class="${r.status}">${esc( r.shot )}</figcaption></figure>` ).join( '' ) }</div>` );

// Keep the last ten runs; screenshots add up fast when this runs every deploy.
readdirSync( root ).sort().slice( 0, -10 ).forEach( d => rmSync( join( root, d ), { recursive: true, force: true } ) );

const fails = results.filter( r => r.status === 'fail' ).length;
const skips = results.filter( r => r.status === 'skip' ).length;
console.log( `\n${results.length - fails - skips} passed, ${fails} failed, ${skips} skipped — ${join( out, 'index.html' )}` );
process.exit( fails ? 1 : 0 );
