@extends('admin/theme')
@section('content')
<style>
    /* Simple table and pagination styles */
    .body-for-users {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        margin: 0;
        padding: 20px;
    }

    .filter-container {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 20px;
        padding: 10px 15px;
        background-color: #f0f0f0;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .filter-container label {
        margin-right: 10px;
        font-size: 16px;
        color: #13414D;
    }

    /* Dropdown Styling */
    .filter-container select {
        padding: 10px 15px;
        border: 1px solid #13414D;
        border-radius: 5px;
        background-color: #ffffff;
        font-size: 16px;
        color: #13414D;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .filter-container select:hover {
        border-color: #13414D;
    }

    .filter-container select:focus {
        outline: none;
        box-shadow: 0 0 5px #13414D;
        border-color: #13414D;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 12px;
        text-align: left;
    }

    th {
        background-color: #13414D;
        color: white;
        font-weight: bold;
    }

    td {
        color: #333;
        font-size: 14px;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #e9f4f5;
    }

    /* Custom pagination styles */
    .pagination {
        display: flex;
        justify-content: center;
        list-style: none;
        padding: 0;
        margin-top: 20px;
    }

    .pagination li {
        margin: 0 5px;
    }

    .pagination li a {
        display: block;
        padding: 10px 16px;
        background-color: #f8f9fa;
        color: #13414D;
        border: 1px solid #ddd;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s, color 0.3s;
    }

    .pagination li a:hover {
        background-color: #13414D;
        color: white;
    }

    .pagination li.active a {
        background-color: #13414D;
        color: white;
        border-color: #13414D;
    }

    .reset-btn {
        margin-left: 15px;
        padding: 10px 15px;
        background-color: #e0e0e0;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .reset-btn:hover {
        background-color: #d1d1d1;
    }

    /* Button for pagination */
    .pagination li span,
    .pagination li a {
        font-size: 14px;
    }

    .whatsapp-link {
        display: inline-block;
        padding: 8px 12px;
        background-color: #25D366;
        /* WhatsApp green */
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s, transform 0.3s;
        font-weight: bold;
        font-size: 14px;
        /* Adjust font size */
    }

    .whatsapp-link:hover {
        background-color: #1ebe5e;
        /* Darker shade on hover */
        transform: scale(1.05);
        /* Slight zoom effect */
    }

    .whatsapp-link:active {
        transform: scale(0.95);
        /* Slight shrink effect on click */
    }

    /* Mobile responsive styles */
    @media (max-width: 768px) {
        table {
            font-size: 12px;
        }

        th,
        td {
            padding: 8px;
        }

        .pagination li a {
            padding: 8px 12px;
            font-size: 12px;
        }
    }
</style>

<div class="body-for-users">
    <div class="filter-container">
        <form action="{{ url()->current() }}" method="GET" id="filterForm">
            <select name="filter" id="filter" onchange="this.form.submit()">
                <option value="">-- Select Filter --</option>
                <option value="highest" {{ request('filter') == 'highest' ? 'selected' : '' }}>Karm Created Highest to Lowest</option>
                <option value="lowest" {{ request('filter') == 'lowest' ? 'selected' : '' }}>Karm Created Lowest to Highest</option>
                <option value="newest" {{ request('filter') == 'newest' ? 'selected' : '' }}>Account Creation Date New to Old</option>
                <option value="oldest" {{ request('filter') == 'oldest' ? 'selected' : '' }}>Account Creation Date Old to New</option>
            </select>
            @if(request('filter'))
            <button type="button" onclick="resetFilter()" class="reset-btn">Reset Filter</button>
            @endif
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Phone Number</th>
                <th>Karm Created</th>
                <th>Account Creation Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sr = 1;
            ?>
            @forelse ($users as $user)
            <tr>
                <td>{{ $sr++ }}</td>
                <td>{{ $user->full_name }}</td>
                <td>{{ $user->phone_number }}</td>
                <td>{{ $user->created_karms_count }}</td>
                <td>{{ $user->formatted_created_at }}</td>
                <td>
                    <a href="https://wa.me/91{{ $user->phone_number }}" class="whatsapp-link" target="_blank"><span>Send WhatsApp Message</span></a>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="6">No users found</td>
            </tr>

            @endforelse
        </tbody>
    </table>

    <!-- Pagination Links -->
    @if ($users->hasPages())
    <ul class="pagination">
        @if ($users->onFirstPage())
        @else
        <li><a href="{{ $users->previousPageUrl() }}&filter={{ request('filter') }}"> Previous</a></li>
        @endif

        @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
        @if ($page == $users->currentPage())
        <li class="active"><a href="#">{{ $page }}</a></li>
        @else
        <li><a href="{{ $url }}&filter={{ request('filter') }}">{{ $page }}</a></li>
        @endif
        @endforeach

        @if ($users->hasMorePages())
        <li><a href="{{ $users->nextPageUrl() }}&filter={{ request('filter') }}">Next </a></li>
        @endif
    </ul>
    @endif
</div>

<script>
    // Function to reset the filter by clearing the sort parameter
    function resetFilter() {
        document.getElementById('filter').value = ''; // Clear the dropdown
        document.getElementById('filterForm').submit(); // Submit the form
    }
</script>

@endsection