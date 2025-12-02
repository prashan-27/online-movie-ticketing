// Constants for seat layout
const ROWS = ['A', 'B', 'C', 'D', 'E','F'];
const SEATS_PER_ROW = 10;

// Initialize seat selection
let selectedSeats = [];
let seatData = [];

// Function to create the seat map
function createSeatMap() {
    const seatMap = document.getElementById('seat-map');
    if (!seatMap) return;

    seatMap.innerHTML = '';
    
    // Create legend
    const legend = document.createElement('div');
    legend.className = 'seat-legend';
    legend.innerHTML = `
        <div class="legend-item"><span class="seat-icon available"></span> Available</div>
        <div class="legend-item"><span class="seat-icon selected"></span> Selected</div>
        <div class="legend-item"><span class="seat-icon booked"></span> Booked</div>
        <div class="legend-item"><span class="seat-icon maintenance"></span> Maintenance</div>
    `;
    seatMap.appendChild(legend);

    // Create screen indicator
    const screen = document.createElement('div');
    screen.className = 'screen';
    screen.textContent = 'SCREEN';
    seatMap.appendChild(screen);

    // Create seat container
    const seatContainer = document.createElement('div');
    seatContainer.className = 'seat-container';
    
    // Create rows and seats
    ROWS.forEach(row => {
        const rowDiv = document.createElement('div');
        rowDiv.className = 'seat-row';
        
        // Add row label
        const rowLabel = document.createElement('div');
        rowLabel.className = 'row-label';
        rowLabel.textContent = row;
        rowDiv.appendChild(rowLabel);

        // Create seats in the row
        for (let i = 1; i <= SEATS_PER_ROW; i++) {
            const seat = document.createElement('div');
            seat.className = 'seat available';
            seat.dataset.row = row;
            seat.dataset.number = i;
            seat.innerHTML = `<span>${i}</span>`;
            rowDiv.appendChild(seat);
            
            // Add event listener for toggling seat type
            seat.addEventListener('click', () => toggleSeatType(seat));
        }

        seatContainer.appendChild(rowDiv);
    });

    seatMap.appendChild(seatContainer);
}

// Function to update seat status based on server data
function updateSeatStatus(seats) {
    seatData = seats;
    const seatElements = document.querySelectorAll('.seat');
    
    seatElements.forEach(seatElement => {
        const row = seatElement.dataset.row;
        const number = parseInt(seatElement.dataset.number);
        
        const seatInfo = seats.find(s => s.row_name === row && s.seat_number === number);
        if (seatInfo) {
            seatElement.className = 'seat ' + seatInfo.booking_status;
            if (seatInfo.status === 'maintenance') {
                seatElement.className = 'seat maintenance';
            }
            
            // Add price tooltip
            seatElement.title = `${seatInfo.type_name} - €${seatInfo.price}`;
            
            // Remove existing click event listeners
            const updatedSeatElement = seatElement.cloneNode(true);
            seatElement.replaceWith(updatedSeatElement);
            
            // Add click handler for available seats
            if (seatInfo.booking_status === 'available' && seatInfo.status !== 'maintenance') {
                updatedSeatElement.addEventListener('click', () => toggleSeatSelection(updatedSeatElement, seatInfo));
            }
        }
    });
}

// Function to toggle seat selection
function toggleSeatSelection(seatElement, seatInfo) {
    const seatIndex = selectedSeats.findIndex(s => s.id === seatInfo.id);
    
    if (seatIndex === -1) {
        // Add seat to selection
        selectedSeats.push(seatInfo);
        seatElement.classList.add('selected');
    } else {
        // Remove seat from selection
        selectedSeats.splice(seatIndex, 1);
        seatElement.classList.remove('selected');
    }
    
    // Update selected seats display and total price
    updateSelectedSeatsInfo();
}

// Function to update selected seats information
function updateSelectedSeatsInfo() {
    const selectedSeatsInfo = document.getElementById('selected-seats-info');
    if (!selectedSeatsInfo) return;

    if (selectedSeats.length === 0) {
        selectedSeatsInfo.innerHTML = '<p>No seats selected</p>';
        return;
    }

    let totalPrice = 0;
    const seatsList = selectedSeats.map(seat => {
        totalPrice += parseFloat(seat.price);
        return `${seat.row_name}${seat.seat_number} (${seat.type_name})`;
    }).join(', ');

    selectedSeatsInfo.innerHTML = `
        <p>Selected Seats: ${seatsList}</p>
        <p>Total Price: ${totalPrice.toFixed(2)}</p>
        <input type="hidden" name="selected_seats" value='${JSON.stringify(selectedSeats.map(s => s.id))}'>
    `;
}

// Function to load seats for selected slot
function loadSeats(slotId) {
    if (!slotId) return;
    
    // Reset selections
    selectedSeats = [];
    
    fetch(`get_seats.php?slot_id=${slotId}`)
        .then(response => response.json())
        .then(seats => {
            updateSeatStatus(seats);
            updateSelectedSeatsInfo();
        })
        .catch(error => console.error('Error loading seats:', error));
}

// Initialize seat selection when document is ready
document.addEventListener('DOMContentLoaded', function() {
  const slotTime = '<?php echo htmlspecialchars($slot_time); ?>';
  if (slotTime) {
    const timeSelect = document.getElementById('slot_id');
    const options = timeSelect.options;
    for (let i = 0; i < options.length; i++) {
      if (options[i].textContent === slotTime) {
        options[i].selected = true;
        break;
      }
    }
    createSeatMap();
  }
});


function toggleSeatType(seatElement) {
  const seatId = seatElement.dataset.seatId;
  const currentType = parseInt(seatElement.dataset.type);
  const newType = document.getElementById('seat-type').value;
  
  if (seatElement.classList.contains('maintenance')) return;
  
  fetch('update_seat_type.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `seat_id=${seatId}&type_id=${newType}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      seatElement.dataset.type = newType;
      seatElement.className = `seat ${newType == 2 ? 'vip' : 'regular'}`;
    }
  });
}