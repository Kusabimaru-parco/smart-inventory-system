// Variable to track previous count so we only play sound on NEW requests
let previousCount = 0;

function checkRequests() {
    // 1. Fetch the data from the API
    fetch('api/api_check_pending.php')
        .then(response => response.json())
        .then(data => {
            let count = parseInt(data.count);
            
            // Get Visual Elements
            let card = document.getElementById('requestCard');
            let badge = document.getElementById('reqBadge');

            // Only run if we are on the Admin Dashboard (elements exist)
            if (card && badge) {
                
                if (count > 0) {
                    // SHOW BADGE & GLOW
                    badge.style.display = 'block';
                    badge.innerText = count;
                    card.classList.add('glow-warning');

                    // TRIGGER NOTIFICATION (Only if count increased)
                    if (count > previousCount) {
                        // Trigger Jane (She handles the "Voice ON/OFF" check internally)
                        if (typeof Jane !== 'undefined') {
                            Jane.sayNotification();
                        }
                    }
                } else {
                    // HIDE BADGE & GLOW
                    badge.style.display = 'none';
                    card.classList.remove('glow-warning');
                }
            }
            
            // Update memory
            previousCount = count;
        })
        .catch(err => console.error('Error polling API:', err));
}

// 2. Run immediately on load
document.addEventListener('DOMContentLoaded', function() {
    checkRequests();
    
    // 3. Set Interval (Check every 10 seconds)
    setInterval(checkRequests, 10000); 
});