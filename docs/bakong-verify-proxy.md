# Bakong Verify Proxy

This is an optional fallback only.

The current project is configured to call the default Bakong API directly from Vercel. Use this proxy only if Bakong asks you to move verification to a separate approved server.

Use this when the Vercel deployment can generate KHQR successfully but Bakong blocks transaction-status checks from Vercel with CloudFront `403`.

## What this does

- keeps the storefront on Vercel
- moves only `check_transaction_by_md5` to a separate PHP host
- lets the Vercel app call that host through `BAKONG_VERIFY_URL`

## Files

- Proxy endpoint: `deploy/bakong-verify-proxy/index.php`
- Config template: `deploy/bakong-verify-proxy/config.example.php`


## Deploy the proxy

Deploy the `deploy/bakong-verify-proxy/` folder to any PHP server that can reach Bakong reliably, such as:

- Cloudways
- shared hosting with PHP 8+
- VPS with Apache / Nginx + PHP

Upload it under your public web root as `bakong-verify/`, then create `config.php` in that folder from `config.example.php`.

Example URL after deploy:

```text
https://verify.your-domain.com/bakong-verify/
```

## Config on the proxy host

Create `bakong-verify/config.php` with:

```env
<?php

return [
    'BAKONG_TOKEN' => 'your_real_bakong_token',
    'BAKONG_API_URL' => 'https://api-bakong.nbc.gov.kh',
    'BAKONG_VERIFY_SECRET' => 'your_shared_secret',
];
```

If you want to test against Bakong SIT instead, switch `BAKONG_API_URL` to
`https://sit-api-bakong.nbc.gov.kh` and use a token issued by the SIT environment.
A production token will not work on the SIT host.

## Environment variables on Vercel

Add these to the Vercel project:

```env
BAKONG_VERIFY_URL=https://verify.your-domain.com/bakong-verify/
BAKONG_VERIFY_SECRET=the_same_shared_secret
```

After saving them, redeploy the Vercel project.

## Request format

The Vercel app sends:

```json
{
  "md5": "transaction-md5"
}
```

If `BAKONG_VERIFY_SECRET` is set, Vercel also sends:

```text
X-Bakong-Verify-Secret: your_shared_secret
```

## Response format

The proxy returns the Bakong JSON response directly, so the existing checkout flow can keep using:

- `responseCode`
- `responseMessage`
- `data`

## Why this is needed

The current production Vercel deployment reaches the checkout page and generates QR codes, but Bakong transaction-status checks are being blocked upstream from Vercel. The proxy avoids that by moving only the verification request to a host Bakong accepts.
