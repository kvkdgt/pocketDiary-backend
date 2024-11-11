@extends('admin/theme')
@section('content')

<style>
    .marketing-admin {
        font-family: Arial, sans-serif;
    }

    .page-title {
        font-size: 24px;
        color: #333;
        margin-bottom: 20px;
    }

    .notification-card {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        max-width: 225px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .notification-image {
        background-color: #f0f0f0;
        border-radius: 8px;
        width: 100%;
        display: flex;
        justify-content: center;
        margin-bottom: 15px;
    }

    .notification-image img {
        width: 100%;
        height: 100%;
    }

    .notification-content h2 {
        font-size: 18px;
        color: #333;
        margin-bottom: 5px;
    }

    .notification-content p {
        font-size: 13px;
        color: #666;
        margin-bottom: 15px;
    }

    .edit-button {
        padding: 10px 20px;
        border: 1px solid #333;
        background-color: #ffffff;
        border-radius: 4px;
        font-size: 14px;
        color: #333;
        width: 100%;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .edit-button:hover {
        background-color: #f0f0f0;
    }
    .notification-content h2 {
        margin: 0 !important;
    }
</style>
<div class="marketing-admin">
    <div class="container">
        <h1 class="page-title">Marketing</h1>
        <div class="notification-card">
            <div class="notification-image">
                <img src="https://img.freepik.com/premium-vector/new-message-notification-notifications-ring-bell-as-reminder-pop-up-smartphone-vector-design_530733-1916.jpg" alt="Notification Icon">
            </div>
            <div class="notification-content">
                <h2>Notification</h2>
                <p>Send targeted push notifications with personalized offers, updates, and announcements to engage your audience instantly.</p>
            </div>
            <button class="edit-button">Go to Notifications</button>
        </div>
    </div>
</div>

@endsection