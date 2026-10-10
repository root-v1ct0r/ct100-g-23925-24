'use strict';

const SERVICE_PRICES = {
  engine_oil: { name: 'Engine Oil Change', price: 3000 },
  brake_inspection: { name: 'Brake Inspection', price: 2000 },
  wheel_alignment: { name: 'Wheel Alignment', price: 3500 },
  computer_diagnosis: { name: 'Computer Diagnosis', price: 2500 },
  full_service: { name: 'Full Service', price: 8000 },
  // Admission number last digit 5: pickup and delivery costs KSh 1,500.
  pickup_delivery: { name: 'Vehicle Pickup & Delivery', price: 1500 }
};

const form = document.getElementById('serviceForm');

if (form) {
  const serviceCheckboxes = [...form.querySelectorAll('input[name="services[]"]')];
  const yearInput = document.getElementById('year');
  const vehicleType = document.getElementById('vehicle_type');
  const serviceDate = document.getElementById('service_date');
  const money = amount => 'KSh ' + amount.toLocaleString('en-KE');

  // Set date limits using the visitor's local date.
  const today = new Date();
  const localToday = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
  serviceDate.min = localToday;
  yearInput.max = String(today.getFullYear());

  function getSelectedServices() {
    return serviceCheckboxes.filter(box => box.checked).map(box => SERVICE_PRICES[box.value]);
  }

  function calculateServiceCost() {
    const selected = getSelectedServices();
    const total = selected.reduce((sum, service) => sum + service.price, 0);
    document.getElementById('estimateTotal').textContent = money(total);
    document.getElementById('selectedCount').textContent = `${selected.length} service${selected.length === 1 ? '' : 's'} selected`;
    document.getElementById('selectedList').textContent = selected.length
      ? selected.map(service => service.name).join(' • ')
      : 'Choose services above to see your selection.';

    const lines = document.getElementById('estimateLines');
    lines.replaceChildren();
    if (!selected.length) {
      const empty = document.createElement('p');
      empty.className = 'empty-line';
      empty.textContent = 'No services selected yet.';
      lines.appendChild(empty);
    } else {
      selected.forEach(service => {
        const row = document.createElement('div');
        row.className = 'estimate-line';
        const name = document.createElement('span');
        name.textContent = service.name;
        const price = document.createElement('strong');
        price.textContent = money(service.price);
        row.append(name, price);
        lines.appendChild(row);
      });
    }
    document.getElementById('services_error').textContent = '';
    return total;
  }

  function checkVehicleAge() {
    const year = Number(yearInput.value);
    const message = document.getElementById('ageMessage');
    message.className = 'hint dynamic-message';
    if (!yearInput.value) {
      message.textContent = '';
    } else if (!Number.isInteger(year) || year < 1950 || year > new Date().getFullYear()) {
      message.textContent = `Enter a year between 1950 and ${new Date().getFullYear()}.`;
      message.classList.add('message-warning');
    } else if (new Date().getFullYear() - year > 15) {
      message.textContent = 'Older vehicle: mention any known faults when the garage contacts you.';
      message.classList.add('message-warning');
    } else {
      message.textContent = 'Year looks reasonable.';
      message.classList.add('message-success');
    }
  }

  function updateVehicleInformation() {
    const info = document.getElementById('vehicleInfo');
    const details = {
      'Saloon Car': 'Saloon cars selected. Please provide the exact model so the garage prepares the correct inspection.',
      'SUV': 'SUV selected. Tell the garage about any 4WD or all-wheel-drive system during confirmation.',
      'Pickup': 'Pickup selected. Include any load-related or suspension concerns in your booking discussion.',
      'Van': 'Van selected. If the vehicle is used commercially, mention this when the garage confirms your booking.'
    };
    info.textContent = details[vehicleType.value] || 'Select a vehicle type to see relevant booking information.';
  }

  function validateServiceForm() {
    const fields = [
      ['customer_name', value => value.trim().length > 0, 'Enter your name.'],
      ['phone', value => /^[0-9]{10}$/.test(value.trim()), 'Enter exactly 10 digits for the phone number.'],
      ['email', value => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value.trim()), 'Enter a valid email address.'],
      ['registration', value => value.trim().length > 0, 'Enter the vehicle registration number.'],
      ['vehicle_type', value => value !== '', 'Select a vehicle type.'],
      ['model', value => value.trim().length > 0, 'Enter the vehicle model.'],
      ['year', value => /^\d{4}$/.test(value) && Number(value) >= 1950 && Number(value) <= new Date().getFullYear(), `Enter a year from 1950 to ${new Date().getFullYear()}.`],
      ['service_date', value => value !== '' && value >= localToday, 'Choose today or a future service date.']
    ];

    let firstInvalid = null;
    fields.forEach(([id, test, message]) => {
      const input = document.getElementById(id);
      const error = document.getElementById(`${id}_error`);
      const valid = test(input.value);
      error.textContent = valid ? '' : message;
      input.classList.toggle('input-invalid', !valid);
      if (!valid && !firstInvalid) firstInvalid = input;
    });

    const hasService = serviceCheckboxes.some(box => box.checked);
    document.getElementById('services_error').textContent = hasService ? '' : 'Select at least one service.';
    if (!hasService && !firstInvalid) firstInvalid = serviceCheckboxes[0];

    if (firstInvalid) {
      document.getElementById('formMessage').textContent = 'Please correct the highlighted fields before submitting.';
      firstInvalid.focus();
      return false;
    }
    document.getElementById('formMessage').textContent = '';
    return true;
  }

  serviceCheckboxes.forEach(box => box.addEventListener('change', calculateServiceCost));
  yearInput.addEventListener('input', checkVehicleAge);
  vehicleType.addEventListener('change', updateVehicleInformation);
  document.getElementById('clearServices').addEventListener('click', () => {
    serviceCheckboxes.forEach(box => { box.checked = false; });
    calculateServiceCost();
  });

  form.querySelectorAll('input, select').forEach(input => {
    input.addEventListener('input', () => {
      input.classList.remove('input-invalid');
      const error = document.getElementById(`${input.id}_error`);
      if (error) error.textContent = '';
      document.getElementById('formMessage').textContent = '';
    });
  });

  form.addEventListener('submit', event => {
    if (!validateServiceForm()) event.preventDefault();
  });

  calculateServiceCost();
  updateVehicleInformation();
}
