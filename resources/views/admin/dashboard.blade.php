@extends('admin/theme')
@section('content')

    <style>
        /* Container styling */
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            font-family: 'Poppins';
        }

        /* Card styling */
        .card {
            background-color: #13414D; /* Dark theme color */
            color: white;
            border-radius: 12px; /* Rounded corners */
            padding: 15px;
            width: 25%; /* Three cards in a row */
            margin: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); /* Soft shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            position: relative;
        }

        /* Hover effect */
        .card:hover {
            transform: translateY(-10px); /* Slight lift effect */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15); /* Shadow gets stronger */
        }

        /* Title inside the card */
        .card h5 {
            font-size: 18px;
            margin: 0;
            margin-bottom: 10px;
            font-weight: 500;
            color: #DDE7EB; /* Lighter shade for headings */
        }

        /* Text inside the card */
        .card p {
            font-size: 28px;
            margin: 0;
            color: #ffffff; /* Bright white for the numbers */
        }

        /* Responsive design for smaller screens */
        @media (max-width: 768px) {
            .card {
                width: 48%; /* Two cards in a row for medium screens */
            }
        }

        @media (max-width: 480px) {
            .card {
                width: 100%; /* Full width for mobile screens */
            }
        }
    </style>

    <div class="container">
        <!-- Total Users -->
        <div class="card">
            <h5>Total Users</h5>
            <p>{{ $totalUsers }}</p> <!-- Replace with dynamic data if available -->
        </div>

        <!-- Today's New Users -->
        <div class="card">
            <h5>Today's New Users</h5>
            <p>{{ $todaysNewUsers }}</p> <!-- Replace with dynamic data if available -->
        </div>

        <!-- Total Karm -->
        <div class="card">
            <h5>Total Karm</h5>
            <p>{{ $totalKarm  }}</p> <!-- Replace with dynamic data if available -->
        </div>

        <!-- Today's Karm -->
        <div class="card">
            <h5>Today's Karm</h5>
            <p>{{ $todaysKarm  }}</p> <!-- Replace with dynamic data if available -->
        </div>

        <!-- Upcoming Karm -->
        <div class="card">
            <h5>Upcoming Karm</h5>
            <p>{{ $upcomingKarm  }}</p> <!-- Replace with dynamic data if available -->
        </div>

        <!-- Previous Karm -->
        <div class="card">
            <h5>Previous Karm</h5>
            <p>{{ $previousKarm  }}</p> <!-- Replace with dynamic data if available -->
        </div>
    </div>

@endsection
