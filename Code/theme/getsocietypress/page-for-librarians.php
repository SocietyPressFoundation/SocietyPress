<?php
/**
 * One-Pager: For Society Librarians (page-for-librarians.php)
 *
 * Aimed at the volunteer (or occasionally paid) librarian running the
 * society's research library. Framing is cataloging, patron access, and
 * getting the collection searchable without a full ILS implementation.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="onepager">
    <div class="container container--narrow">

        <header class="onepager__header">
            <div class="onepager__role-label">For society librarians</div>
            <h1 class="onepager__title">Your collection, cataloged and searchable.</h1>
            <p class="onepager__lede">
                Society libraries sit between a hobbyist's bookshelf and a
                full ILS. SocietyPress's library module is built exactly for
                that middle ground: real cataloging with call numbers and shelf
                locations, a patron-facing search, and none of the overhead of
                running an institutional system.
            </p>
        </header>

        <section class="onepager__section">
            <h2>What you can catalog</h2>
            <ul class="onepager__list">
                <li>Books, monographs, genealogies, family histories</li>
                <li>Periodicals and bound journal runs</li>
                <li>Microfilm, microfiche, DVD/CD collections</li>
                <li>Manuscript collections and archival folders</li>
                <li>Vertical-file material (newspaper clippings, pamphlets, ephemera)</li>
                <li>Maps, plats, atlases</li>
                <li>Research binders (cemetery surveys, transcription projects)</li>
            </ul>
        </section>

        <section class="onepager__section">
            <h2>Catalog features that actually matter</h2>

            <div class="onepager__features">

                <div class="onepager__feature">
                    <h3>Call numbers and shelf locations</h3>
                    <p>Not just &ldquo;item title.&rdquo; Real call-number fields (Dewey, LC, or custom society schemes), shelf / room / drawer location, and physical-description fields. When a patron asks &ldquo;is this book here?&rdquo;, you can tell them.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Cover images from Open Library</h3>
                    <p>Paste an ISBN, get the cover automatically. For items without an ISBN (society-published monographs, manuscript collections), upload a photo yourself.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Patron-facing search</h3>
                    <p>A clean public search interface your members (and visiting researchers) can use from home. Full-text across title, author, subject, description, and notes. Works on phones.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Circulation tracking</h3>
                    <p>Check out, check in, hold, renew. Overdue notices. Patron history. Not required &mdash; many society libraries are in-house-only and never use this &mdash; but it's there when you need it.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Bulk import from CSV</h3>
                    <p>If you already have a catalog in a spreadsheet (most society libraries do), upload it. The importer maps columns to SocietyPress fields, flags duplicates, and warns on missing required data.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Newsletter archive, too</h3>
                    <p>SocietyPress also handles your society's newsletter back-catalog as a separate module. Upload PDFs, automatic cover thumbnails, member-only download access. Many societies treat the newsletter archive as part of the library's remit; the tool supports that.</p>
                </div>

            </div>
        </section>

        <section class="onepager__section onepager__section--highlighted">
            <h2>The librarian role template</h2>
            <p>
                SocietyPress ships with a pre-built <strong>Librarian</strong>
                role that gives you full control over the library module (add,
                edit, delete items, run imports, manage circulation) while
                keeping everything else your board manages out of your way.
                You don't need &ldquo;administrator&rdquo; access just to
                catalog a book.
            </p>
        </section>

        <section class="onepager__section">
            <h2>What it's not</h2>
            <p>
                Honest note: SocietyPress's library is not a full institutional
                ILS (Integrated Library System). It won't replace Koha or Alma
                for a county library. What it will do is give a volunteer-run
                society library a real, modern catalog with meaningful patron
                access, in a form the rest of the society can actually help
                maintain.
            </p>
            <p>
                If you have a catalog of 50,000+ items, multiple branches, or
                complex consortial borrowing, look at Koha. If you have a
                catalog of 500 to 50,000 items and one or two volunteer
                librarians, this is built for you.
            </p>
        </section>

        <footer class="onepager__footer">
            <p>
                <strong>See the library module on the demo:</strong>
                <a href="https://demo.getsocietypress.org">demo.getsocietypress.org</a>
                (the demo library has 19,000+ catalog items to poke around in)
            </p>
            <p>
                <strong>Library-specific questions:</strong>
                <a href="mailto:hello@getsocietypress.org">hello@getsocietypress.org</a>
            </p>
        </footer>

    </div>
</section>

<?php get_footer(); ?>
