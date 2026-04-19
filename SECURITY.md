# Security Policy

Security matters to SocietyPress. Genealogical and historical societies
trust the platform with member personal data, financial records, and in
some cases sensitive genealogical information. Reports of security issues
are taken seriously and addressed promptly.

## Reporting a vulnerability

**Do not file security issues as public GitHub Issues.** Public issues are
visible to anyone who comes across the project, which gives attackers
time to exploit the flaw before a fix is released.

Instead, report vulnerabilities privately by one of these methods:

- **Email:** security@societypress.org
- **GitHub Security Advisories:** use the
  [Report a vulnerability](https://github.com/SocietyPressFoundation/SocietyPress/security/advisories/new)
  form

When you report, please include:

1. A description of the issue and why it matters
2. Steps to reproduce (code, requests, or a proof-of-concept)
3. The affected version(s) of SocietyPress
4. Your name and contact info if you'd like credit in the fix announcement
   (optional — anonymous reports are also welcome)

## What to expect

| Step | Timing |
|---|---|
| Acknowledgment of your report | Within 3 business days |
| Initial assessment and severity rating | Within 7 business days |
| Fix development | Depends on severity and scope |
| Coordinated release | Typically 30–90 days from report |
| Public disclosure and credit | After the fix is released |

High-severity issues (authentication bypass, remote code execution, data
exposure) are prioritized and may ship an out-of-band patch release.

## Supported versions

Only the most recent minor release of SocietyPress receives security
updates. Older versions are not back-patched. Keeping up to date is the
single most effective thing society administrators can do to stay secure.

| Version | Supported |
|---|---|
| 1.0.x | ✅ |
| < 1.0 | ❌ |

## Scope

In scope:

- The SocietyPress plugin (`Code/plugin/societypress.php`)
- The SocietyPress parent theme (`Code/theme/`)
- The five distributed child themes (`Code/theme-*/`)
- The one-click installer (`Code/installer/sp-installer.php`)
- The Softaculous bundle

Out of scope:

- WordPress core itself (report to the WordPress Security Team)
- Third-party plugins and themes
- Hosting infrastructure of the demo or marketing sites
- Physical or social-engineering attacks

## Safe harbor

Good-faith security research that follows this policy will not result in
legal action from the maintainer. Please do not:

- Access or modify data beyond what's necessary to demonstrate the issue
- Disclose the issue publicly before a fix has been released
- Test on sites you don't own without the owner's explicit permission

## Thank you

Independent security researchers make this software safer for every
society using it. Responsible disclosure is appreciated and acknowledged.
