@extends('admin.layouts.admin-layout')

@section('title', 'Testimonials')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="admin-top-bar">
        <h2>Customer Testimonials</h2>
        <a href="{{ route('admin.testimonials.create') }}" class="btn btn-accent">Add Testimonial</a>
    </div>

    @if (session('success'))
        <div class="admin-card" style="border-left:4px solid #2CBFAE;">
            {{ session('success') }}
        </div>
    @endif

    {{-- MAIN TABLE --}}
    <div class="admin-card">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Position / Company</th>
                    <th>Rating</th>
                    <th>Featured</th>
                    <th>Active</th>
                    <th>Created</th>
                    <th style="width:160px;">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($testimonials as $t)
                    <tr>
                        <td>{{ $t->display_order }}</td>

                        <td>{{ $t->customer_name }}</td>

                        <td>
                            @if($t->customer_position) {{ $t->customer_position }} @endif
                            @if($t->customer_company) 
                                {{ $t->customer_position ? ' — ' : '' }}{{ $t->customer_company }}
                            @endif
                        </td>

                        <td>{{ $t->rating ?? '—' }}</td>

                        {{-- FEATURED --}}
                        <td>
                            <span class="badge {{ $t->is_featured ? 'badge-on' : 'badge-off' }}">
                                {{ $t->is_featured ? 'Yes' : 'No' }}
                            </span>
                        </td>

                        {{-- ACTIVE --}}
                        <td>
                            <span class="badge {{ $t->is_active ? 'badge-on' : 'badge-off' }}">
                                {{ $t->is_active ? 'Yes' : 'No' }}
                            </span>
                        </td>

                        <td>{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y') }}</td>

                        {{-- ACTIONS --}}
                        <td>
                            <div class="actions" style="display:flex;gap:6px;">
                                <a href="{{ route('admin.testimonials.edit', $t->id) }}" 
                                   class="btn btn-ghost">Edit</a>

                                <form id="del-{{ $t->id }}" 
                                      action="{{ route('admin.testimonials.destroy', $t->id) }}" 
                                      method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            class="btn btn-danger" 
                                            onclick="confirmDelete({{ $t->id }}, '{{ addslashes($t->customer_name) }}')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8" style="padding:16px;text-align:center;color:#6b7a90;">
                            No testimonials yet. Click “Add Testimonial”.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- DELETE MODAL --}}
    <div id="deleteModal" class="modal-overlay">
        <div class="modal">
            <span class="modal-close" onclick="hideDeleteModal()">&times;</span>
            <h2>Delete Testimonial?</h2>
            <p id="delText">This action cannot be undone.</p>

            <div class="modal-actions">
                <button class="btn btn-ghost" onclick="hideDeleteModal()">Cancel</button>
                <button class="btn btn-danger" id="delConfirmBtn">Delete</button>
            </div>
        </div>
    </div>


    {{-- DELETE SCRIPT --}}
    <script>
        let delFormId = null;

        function confirmDelete(id, name) {
            delFormId = 'del-' + id;
            document.getElementById('delText').innerText =
                `Are you sure you want to delete “${name}”?`;
            document.getElementById('deleteModal').classList.add('active');
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            delFormId = null;
        }

        document.getElementById('delConfirmBtn').addEventListener('click', function () {
            if (delFormId) {
                document.getElementById(delFormId).submit();
            }
        });
    </script>

@endsection
