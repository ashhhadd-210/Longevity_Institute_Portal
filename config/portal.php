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
    'invitation_expiry_days' => (int) env('PORTAL_INVITATION_EXPIRY_DAYS', 14),

];
