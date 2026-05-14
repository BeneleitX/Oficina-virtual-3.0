

class Main {
    constructor() {
        this.intro = document.getElementById('intro');
        this.consentForm = document.getElementById('consent-form');
        this.loadingSection = document.getElementById('loading-section');
        this.webcamSection = document.getElementById('webcam-section');
        this.videoElement = document.getElementById('webcam');
        this.stream = null;
        this.loadModels();

        this.initializeEventListeners();
    }

    async initializeEventListeners() {
        if (this.consentForm) {
            this.consentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.requestWebcamAccess();
            });
        }
    }

    async loadModels() {
        try {
            this.promises = Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri( base_url + 'assets/js/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri(base_url + 'assets/js/models')
            ]).catch(() => {
                console.error('Error loading models');
                this.promises = null;
            });
        } catch (error) {
            console.error('Error loading models');
            this.promises = null;
        }
    }



    async requestWebcamAccess() {
        try {
           

            this.consentForm.classList.add('d-none');
            this.loadingSection.classList.remove('d-none');

            await new Promise(resolve => setTimeout(resolve, 500));
          
                await this.promises;
          

            this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
            this.videoElement.onloadedmetadata = () => {
                this.videoElement.play();
                this.startLivenessCheck();
            };
            this.videoElement.srcObject = this.stream;
            this.webcamSection.classList.remove('d-none');
            this.loadingSection.classList.add('d-none');
            
        } catch (error) {
            console.error('Error accessing webcam:', error);
        }
    }

    async startLivenessCheck() {
        let direction = 'de perfil';
        let flipped = false;
        const processFrame = async () => {
            try {
                const detection = await faceapi.detectSingleFace(
                    this.videoElement,
                    new faceapi.TinyFaceDetectorOptions()
                ).withFaceLandmarks();

                if (detection) {
                    this.webcamSection.querySelector('h5').textContent = 'Por favor, Mira ' + direction;

                    //check if the face is looking to the left or right
                    let leftEye = detection.landmarks.getLeftEye()[0].x;
                    let rightEye = detection.landmarks.getRightEye()[0].x;
                    let eyeDistance = Math.abs(rightEye - leftEye);
                    let nose = detection.landmarks.getNose()[4].x;
                    let lookingLeft = nose < leftEye || Math.abs(leftEye - nose) < eyeDistance * 0.30;
                    let lookingRight = nose > rightEye || Math.abs(rightEye - nose) < eyeDistance * 0.30;
                    let lookingStraight = Math.abs(nose - (leftEye + rightEye) / 2) < eyeDistance * 0.20;
                    
/*                     if(direction == 'a tu izquierda' && lookingLeft) {
                        direction = 'a tu derecha';
                    } else if(direction == 'a tu izquierda' && lookingRight) {
                        direction = 'a tu derecha';
                        flipped = true;
                    } else if(direction == 'a tu derecha' && !flipped && lookingRight) {
                        direction = 'de frente';
                    } else if(direction == 'a tu derecha' && flipped && lookingLeft) {
                        direction = 'de frente';
                    } else if(direction == 'de frente' && detection.detection.score > 0.75 && lookingStraight){
                        if(this._parentWillTakeControl) {
                            this.sendLivenessToParent();
                            return;
                        }
                        this.displayLiveness();
                        return;
                    }
 */

                    if(direction == 'de perfil' && ( lookingLeft || lookingRight )) {
                        direction = 'de frente';
                    } else if(direction == 'de frente' && detection.detection.score > 0.75 && lookingStraight){
             
                        this.displayLiveness();
                        if (this.stream) this.stream.getTracks().forEach(track => track.stop());
                        $( '#vida_error' ).text( '' );

                        key = MD5( escape( Date.now() ) ).substring(1, 10) + '.' + escape(window.btoa( Date.now() ) ).substring(1, 5);

                        window.request.vida_verificado = 1;
                        window.request.valida_vida = key;

                        console.log(window.request.valida_vida);

                        return;
                    }


                } else {
                    this.webcamSection.querySelector('h3').textContent = 'Acercate a la cámara.';
                }
            } catch (error) {
                console.error('Error processing frame:', error);
            }

            setTimeout(() => processFrame(), 250);
        };

        // Start processing frames
        processFrame();
    }


    displayLiveness() {
        this.webcamSection.classList.add('d-none');
        const resultsSection = document.getElementById('results-section');
        resultsSection.classList.remove('d-none');
    }
}


let liveness = new Main();
export default liveness;