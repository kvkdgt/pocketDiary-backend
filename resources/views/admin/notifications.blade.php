@extends('admin/theme')
@section('content')

<style>
    .notifications-admin {
        max-width: 800px;
        margin: 30px auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        font-size: 26px;
        color: #333;
        margin-bottom: 20px;
    }

    .notification-form {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    label {
        font-size: 14px;
        color: #555;
    }

    input[type="text"],
    textarea,
    select {
        padding: 12px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
        background-color: #f9f9f9;
        transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    textarea:focus,
    select:focus {
        border-color: #2596be;
        outline: none;
    }

    textarea {
        min-height: 100px;
        resize: none;
    }

    input[type="file"] {
        padding: 6px;
        border-radius: 6px;
        background-color: #f9f9f9;
    }

    .required {
        color: red;
        font-size: 12px;
    }
    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .alert-success .alert-icon {
        margin-right: 10px;
        font-size: 20px;
    }

    .alert-success button {
        background: none;
        border: none;
        color: #155724;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        transition: color 0.3s;
    }

    .alert-success button:hover {
        color: #0a3c1a;
    }
    .submit-btn {
        padding: 12px 25px;
        background-color: #13414D;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        grid-column: span 2;
    }

    .submit-btn:hover {
        background-color: #1f80a4;
    }

    .form-group .options {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-group .options label {
        font-size: 14px;
        color: #555;
    }

    .form-group .options select {
        width: 100%;
    }

    /* Image preview styling */
    .image-preview {
        margin-top: 10px;
        max-width: 200px;
        max-height: 200px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #ccc;
        display: none;
    }

    /* Ensuring that labels and input fields on the same line work well on small screens */
    @media (max-width: 768px) {
        .notification-form {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="notifications-admin">
    <h2>Send Notification</h2>
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    <form action="{{ route('admin/marketing/sendCustomNotification') }}" method="POST" enctype="multipart/form-data" class="notification-form">
        @csrf
        <!-- Notification Title -->
        <div class="form-group">
            <label for="notification_title">Notification Title <span class="required">*</span></label>
            <input type="text" id="notification_title" name="notification_title" required placeholder="Enter notification title">
        </div>

        <!-- User Selection Option -->
        <div class="form-group options">
            <label for="user_select">Send To:</label>
            <select name="user_select" id="user_select">
                <option value="all">All Users</option>
                <option value="today_joined">Today Joined</option>

                <!-- <option value="selected">Selected Users</option> -->
            </select>
        </div>

        <!-- Notification Description -->
        <div class="form-group">
            <label for="notification_description">Notification Description <span class="required">*</span></label>
            <textarea id="notification_description" name="notification_description" required placeholder="Enter notification description"></textarea>
        </div>

        <!-- Notification Image (Optional) -->
        <div class="form-group">
            <label for="notification_image">Notification Image (Optional)</label>
            <input type="file" id="notification_image" name="notification_image" accept="image/*" onchange="previewImage(event)">
            <img id="image_preview" class="image-preview" src="" alt="Image Preview">
       
        </div>

        <!-- Image Preview -->

        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="submit-btn">Send Notification</button>
        </div>
    </form>
</div>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('image_preview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';  // Show the image preview
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';  // Hide if no file selected
        }
    }
</script>

@endsection
