# AutoCare Garage Vehicle Service Booking System

A student project built with HTML, CSS, JavaScript, and PHP. It is designed to run locally through XAMPP or another PHP-enabled web server.

## Included files

- `index.html`: Home page and service overview.
- `service.html`: Booking form, browser validation, live service summary, and live estimate.
- `process_service.php`: POST handler, server-side validation, independent price calculation, and confirmation page.
- `assets/style.css`: Responsive styles.
- `assets/app.js`: JavaScript validation, dynamic vehicle information, age feedback, service selection, and cost calculator.
- `screenshots/`: Practical demonstration screenshot placeholders and capture instructions.

## Individualization requirement

This package implements requirement **5**, for admission numbers whose required digit is 5: **Vehicle Pickup & Delivery, KSh 1,500**. It appears in the service selection list, is included in the JavaScript estimate, and is independently priced by PHP. The PHP total is recalculated from the accepted service keys rather than trusting a total sent by the browser.

## Service price list

| Service | Price |
|---|---:|
| Engine Oil Change | KSh 3,000 |
| Brake Inspection | KSh 2,000 |
| Wheel Alignment | KSh 3,500 |
| Computer Diagnosis | KSh 2,500 |
| Full Service | KSh 8,000 |
| Vehicle Pickup & Delivery | KSh 1,500 |

## Run using XAMPP on Windows

1. Install and open XAMPP.
2. Start **Apache** from the XAMPP Control Panel.
3. Copy the complete `autocare_vehicle_service` folder into `C:\\xampp\\htdocs\\`.
4. Open a browser and visit `http://localhost/autocare_vehicle_service/`.
5. Select **Book a Service**, fill in the form, choose one or more services, and submit.
6. The PHP confirmation page should show the customer and vehicle information, chosen services, booking reference, and server-calculated total.

Do not open `index.html` directly with `file://`. PHP runs through Apache, not by opening the file in the browser.

If your XAMPP installation uses a different folder, place the project in that installation's `htdocs` directory. Ensure PHP is enabled in Apache.

## Suggested practical demonstration

Capture your own screenshots after running the project in XAMPP. Save them into `screenshots/` using these names:

1. `01-home-page.png`: Open `http://localhost/autocare_vehicle_service/`.
2. `02-booking-form.png`: Show customer and vehicle fields before submission.
3. `03-live-estimate.png`: Select Engine Oil Change, Brake Inspection, and Computer Diagnosis. Expected total: **KSh 7,500**. The sample in the brief uses different prices for some services, so this project's listed price table is the source of truth.
4. `04-pickup-delivery.png`: Select Vehicle Pickup & Delivery with Engine Oil Change. Expected total: **KSh 4,500**.
5. `05-validation.png`: Leave a required field blank or enter a 9-digit phone number, then submit. The form should stop and show a validation message.
6. `06-confirmation.png`: Submit a valid sample booking and show the PHP confirmation with the selected services and estimated total.

The files in `screenshots/` include a checklist, not fabricated screenshots. Take real screenshots of the running system for your practical submission.

## Sample test values

- Customer name: Jane Wanjiku
- Phone: 0712345678
- Email: jane@example.com
- Registration: KDA 123A
- Vehicle type: SUV
- Model: Toyota RAV4
- Year: 2018
- Preferred date: choose today or a future date
- Services: Engine Oil Change and Vehicle Pickup & Delivery
- Expected total: KSh 4,500

## Validation and implementation notes

- JavaScript validates required fields, a 10-digit phone number, email format, registration, vehicle type, model, year, date, and service selection.
- PHP repeats important checks on the server and rejects invalid service keys.
- The service estimate updates as service checkboxes change. The clear-selection button is also provided.
- The server uses `htmlspecialchars` for output escaping.
- The project does not store booking records in a database or send emails. The confirmation is generated for demonstration purposes.
- The currency amounts are estimates and should be confirmed by the garage before work begins.
