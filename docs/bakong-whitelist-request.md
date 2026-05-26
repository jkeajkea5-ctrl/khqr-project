# Bakong Verification Access Request

Use this message if the Vercel deployment can generate KHQR successfully but Bakong verification returns `403 Forbidden` from production.

## Current production details

- Website: `https://khqr-project.vercel.app`
- Verify API used: `POST /v1/check_transaction_by_md5`
- Hosting: Vercel production deployment
- Current Bakong mode: direct Bakong API from the Vercel app

## Email / support message template

Subject: Request to allow Bakong transaction verification for production website

Hello Bakong/NBC Support,

I am integrating KHQR payment verification for my production website and need help allowing the Bakong transaction-status API request from my deployed application.

Current situation:

- KHQR generation works on production.
- The production website can create the QR successfully.
- The verification request is failing on production with `HTTP 403 Forbidden`.
- The verify endpoint we use is `POST /v1/check_transaction_by_md5`.

Production website:

- `https://khqr-project.vercel.app`

Please let me know what you require so this production deployment can access Bakong transaction verification successfully.

If needed, I can provide:

- merchant Bakong account ID
- registered email or organization
- project name
- hosting platform details
- request timestamps
- sample failed verification responses

Thank you.

## Short version

Hello Bakong Support, my production site `https://khqr-project.vercel.app` can generate KHQR successfully, but server-side transaction verification with `POST /v1/check_transaction_by_md5` is returning `HTTP 403 Forbidden`. Please let me know what information or approval is required so my production deployment can access the verification API.
