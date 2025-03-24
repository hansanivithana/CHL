<?php
// Handle form submission
$statusMessage = '';
$alertType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate required fields
        $requiredFields = ['packageName', 'description', 'instructorID', 'price'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill all required fields!");
            }
        }

        // File upload handling
        $uploadPaths = [
            'learningMaterials' => [
                'dir' => 'Uploads/Learning_Materials/',
                'types' => ['pdf', 'doc', 'docx', 'txt'],
                'path' => ''
            ],
            'videoLessons' => [
                'dir' => 'Uploads/Video_Lessons/',
                'types' => ['mp4', 'mov', 'avi'],
                'path' => ''
            ]
        ];

        foreach ($uploadPaths as $field => $config); {
            if ($_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $fileExt = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                
                if (!in_array($fileExt, $config['types'])) {
                    throw new Exception("Invalid file type for $field!");
                }

                $uploadDir = $config['dir'] . $_POST['instructorID'] . '/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = uniqid() . '_' . basename($_FILES[$field]['name']);
                $targetPath = $uploadDir . $fileName;

                if (!move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath)) {
                    throw new Exception("Failed to upload $field!");
                }

                $uploadPaths[$field]['path'] = $targetPath;
            }
        }

        // Prepare API data
        $packageData = [
            'packageID' => 0,
            'packageName' => $_POST['packageName'],
            'description' => $_POST['description'],
            'instructorID' => (int)$_POST['instructorID'],
            'price' => (float)$_POST['price'],
            'learningMaterials' => $uploadPaths['learningMaterials']['path'],
            'videoLessons' => $uploadPaths['videoLessons']['path'],
            'dateAdded' => date('c') // ISO 8601 format
        ];

        // API call
        $ch = curl_init('https://localhost:7150/api/LearningPackage/AddLearningPackage');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'accept: */*',
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($packageData),
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('API Connection Error: ' . curl_error($ch));
        }

        $responseData = json_decode($response, true);
        $statusMessage = $responseData['statusMessage'] ?? 'Package submitted successfully';
        $alertType = $statusCode === 200 ? 'success' : 'danger';

        curl_close($ch);

    } catch (Exception $e) {
        $statusMessage = $e->getMessage();
        $alertType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Learning Package</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .upload-preview {
            border: 2px dashed #dee2e6;
            padding: 1rem;
            margin-top: 0.5rem;
            border-radius: 8px;
        }
        .preview-item {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4 text-center text-primary">Create New Learning Package</h2>

            <?php if ($statusMessage): ?>
                <div class="alert alert-<?= $alertType ?> alert-dismissible fade show">
                    <?= htmlspecialchars($statusMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Package Name -->
                    <div class="col-md-6">
                        <label class="form-label">Package Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="packageName" required>
                    </div>

                    <!-- Instructor ID -->
                    <div class="col-md-6">
                        <label class="form-label">Instructor ID <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="instructorID" required>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>

                    <!-- Price -->
                    <div class="col-md-6">
                        <label class="form-label">Price ($) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="price" step="0.01" required>
                    </div>

                    <!-- File Uploads -->
                    <div class="col-md-6">
                        <label class="form-label">Learning Materials (PDF, DOC) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="learningMaterials" accept=".pdf,.doc,.docx" required>
                        <div class="upload-preview" id="materialPreview"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Video Lessons (MP4) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="videoLessons" accept="video/mp4" required>
                        <div class="upload-preview" id="videoPreview"></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            Create Learning Package
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File preview functionality
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const previewId = this.name + 'Preview';
                const previewDiv = document.getElementById(previewId);
                previewDiv.innerHTML = '';

                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        
                        if (file.type.startsWith('video/')) {
                            previewItem.innerHTML = `
                                <video controls width="100%">
                                    <source src="${e.target.result}" type="${file.type}">
                                    Your browser does not support video preview.
                                </video>
                            `;
                        } else {
                            previewItem.innerHTML = `
                                <div class="text-success">
                                    <i class="bi bi-file-earmark-text fs-4"></i>
                                    ${file.name}
                                </div>
                            `;
                        }
                        
                        previewDiv.appendChild(previewItem);
                    }

                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>