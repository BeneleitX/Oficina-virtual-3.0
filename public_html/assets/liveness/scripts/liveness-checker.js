class LivenessChecker {
    constructor() {

    }

    checkLiveness(params = {}) {
        return new Promise((resolve, reject) => {
            let settled = false;
            const nonce = Math.random().toString(36).substring(2, 15);
            if(!newTab) {
                reject('POPUP_BLOCKED');
                return;
            }

            function cleanup() {
                window.removeEventListener('message', handler);
                clearInterval(tabCheckInterval);
            }

            function handler(event) {
                if(event.data.type === 'liveness-check-result') {
                    if(event.data.nonce !== nonce) return;
                    settled = true;
                    resolve();
                    newTab.close();
                    cleanup();
                } 
            }

            window.addEventListener('message', handler);

            // Check if the tab is closed before resolving/rejecting
            const tabCheckInterval = setInterval(() => {
                if (newTab.closed && !settled) {
                    settled = true;
                    reject('CANCELLED');
                    cleanup();
                }
            }, 500);
        });
    }
}

const livenessChecker = new LivenessChecker();
export default livenessChecker;