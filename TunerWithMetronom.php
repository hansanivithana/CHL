<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Music Instrument Tuner</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      margin: 0;
      padding: 20px;
      background-color: #f0f0f0;
    }
    #note {
      font-size: 3em;
      margin: 20px 0;
    }
    .in-tune {
      color: green;
    }
    .out-of-tune {
      color: red;
    }
    #status {
      font-size: 1.5em;
      margin-top: 10px;
    }
    #startBtn {
      padding: 10px 20px;
      font-size: 18px;
      cursor: pointer;
      margin-top: 20px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
    }
    #startBtn:disabled {
      background-color: #ccc;
    }
  </style>
</head>
<body>

  <h1>Instrument Tuner</h1>
  <button id="startBtn">Start Tuner</button>
  <div id="note"></div>
  <div id="status"></div>

  <script>
    const startBtn = document.getElementById("startBtn");
    const noteDisplay = document.getElementById("note");
    const statusDisplay = document.getElementById("status");

    let audioContext;
    let analyser;
    let microphone;
    let isTuning = false;

    // Array of musical notes with their standard frequencies (e.g., for a guitar).
    const notes = [
      { name: "C", frequency: 261.63 },
      { name: "C#", frequency: 277.18 },
      { name: "D", frequency: 293.66 },
      { name: "D#", frequency: 311.13 },
      { name: "E", frequency: 329.63 },
      { name: "F", frequency: 349.23 },
      { name: "F#", frequency: 369.99 },
      { name: "G", frequency: 392.00 },
      { name: "G#", frequency: 415.30 },
      { name: "A", frequency: 440.00 },
      { name: "A#", frequency: 466.16 },
      { name: "B", frequency: 493.88 },
    ];

    // Start tuner when the button is clicked
    function startTuner() {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Your browser does not support audio input.");
        return;
      }

      navigator.mediaDevices.getUserMedia({ audio: true })
        .then(function(stream) {
          audioContext = new (window.AudioContext || window.webkitAudioContext)();
          analyser = audioContext.createAnalyser();
          microphone = audioContext.createMediaStreamSource(stream);
          microphone.connect(analyser);
          analyser.fftSize = 2048;

          detectPitch(); // Start pitch detection
          startBtn.disabled = true;
          startBtn.innerHTML = "Tuning...";
        })
        .catch(function(err) {
          alert("Error accessing the microphone: " + err);
        });
    }

    // Detect pitch from the audio stream
    function detectPitch() {
      if (audioContext && analyser) {
        const bufferLength = analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);

        analyser.getByteFrequencyData(dataArray); // Get frequency data

        const pitch = getPitch(dataArray); // Calculate pitch from data
        updateDisplay(pitch); // Update the display with the detected pitch

        requestAnimationFrame(detectPitch); // Continuously detect pitch
      }
    }

    // Get the pitch (frequency) from the frequency data array
    function getPitch(dataArray) {
      const maxIndex = dataArray.indexOf(Math.max(...dataArray));
      const nyquist = audioContext.sampleRate / 2;
      const frequency = maxIndex * nyquist / dataArray.length;

      return frequency;
    }

    // Update the display with the closest note and tuning status
    function updateDisplay(frequency) {
      const closestNote = getClosestNote(frequency);
      const noteName = closestNote.name;
      const noteFrequency = closestNote.frequency;

      noteDisplay.innerHTML = noteName;

      if (Math.abs(frequency - noteFrequency) < 5) {
        noteDisplay.className = "in-tune";
        statusDisplay.innerHTML = "In Tune!";
      } else {
        noteDisplay.className = "out-of-tune";
        statusDisplay.innerHTML = "Out of Tune";
      }
    }

    // Find the closest musical note for the detected frequency
    function getClosestNote(frequency) {
      let closestNote = notes[0];
      let minDiff = Math.abs(frequency - closestNote.frequency);

      for (let i = 1; i < notes.length; i++) {
        const diff = Math.abs(frequency - notes[i].frequency);
        if (diff < minDiff) {
          closestNote = notes[i];
          minDiff = diff;
        }
      }

      return closestNote;
    }

    // Event listener for the "Start Tuner" button
    startBtn.addEventListener("click", startTuner);
  </script>

</body>
</html>
