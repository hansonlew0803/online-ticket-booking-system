window.Echo.channel('bookings')
    .listen('BookingCreated', (e) => {
        console.log('Booking created:', e.booking);
        // Update the UI or perform other actions with the booking data
    });