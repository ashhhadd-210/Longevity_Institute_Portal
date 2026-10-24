<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Institutional configuration
    |--------------------------------------------------------------------------
    */

    // Version string of the privacy notice collaborators consent to at
    // registration. Changing it requires re-consent (doc 08 §3). The wording
    // itself is reviewed by the Institute's counsel.
    'privacy_notice_version' => env('PORTAL_PRIVACY_NOTICE_VERSION', '2026-08-draft'),

    // Invitation links are single-use and expire (doc 08 §1).
    // Long enough for people who answer invitations weeks later (the Institute,
    // October 2026). An expired link can always be reissued in one click.
    'invitation_expiry_days' => (int) env('PORTAL_INVITATION_EXPIRY_DAYS', 90),

    // Where profile photographs live. "public" on a single server; the
    // Institute switches this to the s3 disk (DigitalOcean Spaces) by setting
    // the environment variable, with no change to code.
    'photo_disk' => env('PORTAL_PHOTO_DISK', 'public'),

    // The Collaborator Profile Standards shown on first entry. Raise the version
    // when the wording changes and everybody is shown the new text once.
    'profile_standards_version' => env('PORTAL_PROFILE_STANDARDS_VERSION', '1.0'),

];
