<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All for Music</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        .slideshow-container {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .carousel-item img {
            width: 100%;
            height: 100vh;
            object-fit: cover;
            filter: blur(5px);
        }

        .overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.85);
            padding: 12px;
        }

        .navbar a {
            color: gold !important;
            font-size: 18px;
            transition: 0.3s;
        }

        .navbar a:hover {
            color: #ffd700 !important;
        }

        .hero-card {
            position: absolute;
            top: 22%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.75);
            color: gold;
            padding: 35px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0px 0px 25px rgba(255, 215, 0, 0.6);
            max-width: 800px;
            width: 90%;
        }

        .hero-card h1 {
            font-size: 42px;
            font-weight: bold;
            text-shadow: 2px 2px 8px black;
        }

        .hero-card p {
            font-size: 17px;
            font-style: italic;
        }

        .video-container {
            position: absolute;
            bottom: 15%;
            width: 100%;
            text-align: center;
        }

        video {
            width: 70%;
            max-width: 750px;
            border-radius: 12px;
            box-shadow: 0px 0px 18px rgba(255, 215, 0, 0.5);
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.85);
            color: gold;
            text-align: center;
            padding: 12px 0;
        }

        .footer a {
            color: gold;
            margin: 0 8px;
            text-decoration: none;
        }

        .footer a:hover {
            color: #ffd700;
        }

        @media (max-width: 768px) {
            .hero-card h1 {
                font-size: 34px;
            }

            .hero-card p {
                font-size: 15px;
            }

            video {
                width: 80%;
            }
        }

        @media (max-width: 480px) {
            .hero-card {
                padding: 20px;
            }

            .hero-card h1 {
                font-size: 28px;
            }

            .hero-card p {
                font-size: 14px;
            }

            video {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <?php $role = $_GET['role'] ?? 'User'; ?>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">All for Music</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Play</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">MarketPlace</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Tuner</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Metronomme</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">SingUp</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">LogIn</a></li>

                </ul>
            </div>
        </div>
    </nav>

    <div class="slideshow-container">
        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="http://localhost/All_FOR_MUSIC/Icons/HomePage/CreativeImage.jpg" alt="Music Image 1" loading="lazy">
                </div>
                <div class="carousel-item">
                    <img src="http://localhost/All_FOR_MUSIC/Icons/HomePage/Growimage.jpg" alt="Music Image 2" loading="lazy">
                </div>
                <div class="carousel-item">
                    <img src="http://localhost/All_FOR_MUSIC/Icons/HomePage/LearnImage.jpg" alt="Music Image 3" loading="lazy">
                </div>
                <div class="carousel-item">
                    <img src="http://localhost/All_FOR_MUSIC/Icons/HomePage/PlayImage.jpg" alt="Music Image 4" loading="lazy">
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        <div class="overlay"></div>
    </div>

    <div class="hero-card">
        <h1>ALL FOR MUSIC</h1>
        <p><strong>A collaborative and inclusive platform for musicians, learners, and artists to connect, share, and grow.</strong></p>
        <p><strong>Breaking barriers, inspiring creativity, and empowering musicians worldwide.</strong></p>
    </div>

    <div class="video-container">
        <h2 style="color: gold;">Welcome to All for Music</h2>
        <video autoplay loop muted>
            <source src="http://localhost/All_FOR_MUSIC/Icons/HomePage/Allformusic.mov" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

    <footer class="footer">
        <p>© 2024 All Rights Reserved | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
        <p>Follow us on: <a href="#">Facebook</a> | <a href="#">Twitter</a> | <a href="#">Instagram</a></p>
    </footer>
</body>
</html>
