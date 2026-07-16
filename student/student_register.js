const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const captureBtn = document.getElementById('captureBtn');
const retakeBtn = document.getElementById('retakeBtn');
const webcam_input = document.getElementById('webcam_image');
let stream = null; // to store stream so we can stop it

// 1. Turn on webcam
navigator.mediaDevices.getUserMedia({ video: { width: 320, height: 240 } })
  .then(s => { 
    stream = s;
    video.srcObject = stream; 
  })
  .catch(err => { alert("Camera error: " + err.message); });

// 2. Capture photo
captureBtn.addEventListener('click', () => {
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0);
  
  const image_data = canvas.toDataURL('image/png');
  webcam_input.value = image_data; // send to PHP

  // STOP WEBCAM and SWAP
  stream.getTracks().forEach(track => track.stop()); // turn off camera light
  video.style.display = 'none';  // hide video
  canvas.style.display = 'block'; // show canvas with photo in same frame

  // swap buttons
  captureBtn.style.display = 'none';
  retakeBtn.style.display = 'inline-block';
});

// 3. Retake photo
retakeBtn.addEventListener('click', () => {
  navigator.mediaDevices.getUserMedia({ video: { width: 320, height: 240 } })
  .then(s => { 
    stream = s;
    video.srcObject = stream; 
    video.style.display = 'block';
    canvas.style.display = 'none';
    webcam_input.value = ""; // clear old image

    captureBtn.style.display = 'inline-block';
    retakeBtn.style.display = 'none';
  });
});