const Jane = {
    // 1. STATE MANAGEMENT
    isEnabled: localStorage.getItem('jane_voice_enabled') !== 'false', 
    lastIndex: -1, 
    currentAudio: null, 

    // 2. FILE PATHS
    sounds: {
        welcome: ['welcome_1.mp3', 'welcome_2.mp3'],
        toggle_on: 'voice_on.mp3',
        toggle_off: 'voice_off.mp3',
        notifs: [
            'notif_1.mp3', 'notif_2.mp3', 'notif_3.mp3', 'notif_4.mp3',
            'notif_5.mp3', 'notif_6.mp3', 'notif_7.mp3', 'notif_8.mp3'
        ]
    },

    // 3. CORE FUNCTIONS
    play: function(filename) {
        // --- LOGIC FIX HERE ---
        // Allow playing if Enabled OR if it's one of the Toggle sounds (On/Off)
        if (!this.isEnabled && 
            filename !== this.sounds.toggle_on && 
            filename !== this.sounds.toggle_off) {
            return; 
        }
        
        // Interrupt previous audio
        if (this.currentAudio) {
            this.currentAudio.pause();
            this.currentAudio.currentTime = 0;
        }

        // Play
        this.currentAudio = new Audio('assets/sounds/' + filename);
        this.currentAudio.play().catch(e => console.log("Browser blocked audio. Interact with page first."));
    },

    // Toggle Button Logic
    toggle: function() {
        this.isEnabled = !this.isEnabled;
        localStorage.setItem('jane_voice_enabled', this.isEnabled);
        
        this.updateUI();

        // Play feedback sound
        if (this.isEnabled) {
            this.play(this.sounds.toggle_on);
        } else {
            // Now this will work because we added the exception in play()
            this.play(this.sounds.toggle_off); 
        }
    },

    updateUI: function() {
        const btn = document.getElementById('janeToggleBtn');
        const icon = document.getElementById('janeIcon');
        const status = document.getElementById('janeStatus');

        if (btn && icon && status) {
            if (this.isEnabled) {
                btn.classList.replace('btn-outline-secondary', 'btn-outline-primary');
                icon.classList.replace('bi-mic-mute-fill', 'bi-mic-fill');
                status.innerText = "ON";
            } else {
                btn.classList.replace('btn-outline-primary', 'btn-outline-secondary');
                icon.classList.replace('bi-mic-fill', 'bi-mic-mute-fill');
                status.innerText = "OFF";
            }
        }
    },

    // 4. SCENARIO LOGIC
    sayWelcome: function() {
        if (!this.isEnabled) return;

        // --- NEW LOGIC: CHECK IF ALREADY PLAYED ---
        if (sessionStorage.getItem('jane_welcome_played')) {
            return; // Stop. We already said welcome in this session.
        }
        // ------------------------------------------

        const index = Math.floor(Math.random() * this.sounds.welcome.length);
        this.play(this.sounds.welcome[index]);

        // --- NEW LOGIC: MARK AS PLAYED ---
        sessionStorage.setItem('jane_welcome_played', 'true');
    },

    sayNotification: function() {
        if (!this.isEnabled) return;
        
        let index;
        do {
            index = Math.floor(Math.random() * this.sounds.notifs.length);
        } while (index === this.lastIndex && this.sounds.notifs.length > 1);

        this.lastIndex = index;
        this.play(this.sounds.notifs[index]);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Jane.updateUI();
    setTimeout(() => Jane.sayWelcome(), 1000); 
});