<?php
if (!isset($_GET['id'])) {
    echo "<script>alert('No lyric selected!'); window.location.href='index.php';</script>";
    exit();
}

$lyricID = intval($_GET['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Lyric</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Update Music Lyric</h2>
    <form id="updateForm">
        <input type="hidden" name="lyricID" value="<?php echo $lyricID; ?>">
        <div class="mb-3">
            <label>Title:</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Content:</label>
            <textarea name="content" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-warning">Update</button>
    </form>
</div>
</body>
</html>
