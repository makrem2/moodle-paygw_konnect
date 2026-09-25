# Konnect payment gateway for Moodle (`paygw_konnect`)

An **unofficial, community** payment gateway plugin that lets a Moodle site take
course and enrolment payments through **Konnect** (https://konnect.network), a
Tunisian payment gateway. Payments are charged in **Tunisian Dinar (TND)**.

> This project is not affiliated with or endorsed by Konnect Networks or Moodle.

## Features
- Adds "Konnect" as a gateway in Moodle's core Payments subsystem.
- Redirect-based checkout: the student is sent to the Konnect hosted pay page
  (wallet, bank card, e-DINAR) and returned to Moodle.
- Server-to-server **webhook** plus browser **return** verification, so an
  enrolment is delivered only after Konnect confirms the payment as `completed`.
- Idempotent delivery (a lock + status column) so the webhook and the browser
  return can never double-enrol.
- Sandbox and production environments, selectable per payment account.

## Requirements
- Moodle 5.x (developed and tested on 5.2.1).
- A public site reachable over **HTTPS** (Konnect must be able to call your
  return URL and webhook; `localhost` will not work).
- A Konnect account with an **API key** (`x-api-key`) and a **receiver wallet ID**.
- Course/enrolment fee currency set to **TND** (the currency this plugin declares).

## Installation
### From ZIP (Moodle UI)
1. Download this repository (Code > Download ZIP) or a release ZIP.
2. Make sure the folder inside is named `konnect` (rename if a suffix like
   `-main` was added).
3. In Moodle: Site administration > Plugins > Install plugins, upload the ZIP,
   and complete the database upgrade.

### Manual (FTP / cPanel)
Copy the plugin so its files land at:
```
<moodle>/payment/gateway/konnect/
```
so that `version.php` is at `<moodle>/payment/gateway/konnect/version.php`, then
log in as an admin and complete the upgrade prompt.

### Git
```
cd <moodle>/payment/gateway
git clone https://github.com/<you>/moodle-paygw_konnect.git konnect
```

## Configuration
1. Site administration > Payments > Payment accounts > create an account.
2. On that account, open the **Konnect** gateway, tick **Enabled**, and set:
   - **API key** - your Konnect `x-api-key`.
   - **Receiver wallet ID** - the wallet that receives funds.
   - **Environment** - Sandbox for testing, Production when live.
3. Sell a course: enable **Enrolment on payment**, add it to the course, select
   the payment account, set the **currency to TND**, and enter the fee.

## How it works
- `pay.php` reads the gateway config, converts the fee to **millimes**
  (1 TND = 1000 millimes) and calls `POST /payments/init-payment` with the
  `x-api-key` header, stores the returned `paymentRef`, then redirects the
  student to the Konnect `payUrl`.
- `callback.php` (browser return) and `webhook.php` (server-to-server) both
  re-verify via `GET /payments/{ref}` and deliver the enrolment only when the
  status is `completed`.
- API base URLs: sandbox `https://api.sandbox.konnect.network/api/v2`,
  production `https://api.konnect.network/api/v2`.

## Troubleshooting
- **`No define call for paygw_konnect/gateways_modal`** when clicking Continue:
  re-upload the `amd` folder, then purge caches (Site administration >
  Development > Purge caches) and hard-refresh. Moodle serves JavaScript from a
  cached, versioned bundle.
- **`syntax error, unexpected token`** after editing a file: a straight quote was
  replaced by a curly/"smart" quote. Keep all PHP ASCII; delete the folder, purge
  caches, and re-upload a clean copy.
- **Konnect not offered**: the fee currency must be TND and the gateway must be
  enabled on the selected payment account.
- **Paid but not enrolled**: Konnect could not reach your webhook (site must be
  public over HTTPS), or the API key/environment did not match.

## Security
No secrets are stored in the code. The API key and wallet ID are entered in the
Moodle admin UI and kept in Moodle's gateway configuration. Never commit real
credentials to this repository.

## License
GNU GPL v3 or later - see the `LICENSE` file. Moodle and its plugins are licensed
under the GPL.
