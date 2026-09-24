@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1>Dashboard</h1>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-value">{{ $stats['total'] }}</span>
            <span class="stat-label">Total</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $stats['todo'] }}</span>
            <span class="stat-label">To Do</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $stats['in_progress'] }}</span>
            <span class="stat-label">In Progress</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $stats['completed'] }}</span>
            <span class="stat-label">Completed</span>
        </div>
        <div class="stat-card stat-card-overdue">
            <span class="stat-value">{{ $stats['overdue'] }}</span>
            <span class="stat-label">Overdue</span>
        </div>
    </div>

    <div class="card filters-card">
        <div class="filters-form">
            <div class="form-group search-group">
                <label for="search" class="sr-only">Search tasks</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    placeholder="Search tasks by title or description..."
                    class="form-control"
                >
            </div>

            <div class="form-row filters-row">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="all">All</option>
                        @foreach (\App\Models\Task::STATUSES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="all">All</option>
                        @foreach (\App\Models\Task::PRIORITIES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="due">Due Date</label>
                    <select id="due" name="due" class="form-control">
                        <option value="all">All</option>
                        <option value="overdue">Overdue</option>
                        <option value="today">Due Today</option>
                        <option value="this_week">Due This Week</option>
                        <option value="no_due_date">No Due Date</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sort">Sort By</label>
                    <select id="sort" name="sort" class="form-control">
                        @foreach (['created_at' => 'Created Date', 'due_date' => 'Due Date', 'priority' => 'Priority', 'title' => 'Title', 'status' => 'Status'] as $value => $label)
                            <option value="{{ $value }}" @selected($value === 'created_at')>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="direction">Order</label>
                    <select id="direction" name="direction" class="form-control">
                        <option value="desc" selected>Descending</option>
                        <option value="asc">Ascending</option>
                    </select>
                </div>

                <div class="form-group filters-actions">
                    <label class="sr-only">Apply</label>
                    <button type="button" id="filters-reset" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        @if ($stats['total'] === 0)
            <div class="empty-state">
                <p>No tasks found.</p>
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">+ Add your first task</a>
            </div>
        @else
            <div id="tasks-grid" class="ag-theme-quartz" style="width: 100%; height: 560px;" data-api-url="{{ url('/api/tasks') }}?per_page=1000"></div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31/styles/ag-grid.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31/styles/ag-theme-quartz.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community@31/dist/ag-grid-community.min.js"></script>
    <script>
        (function () {
            var gridContainer = document.getElementById('tasks-grid');
            var apiUrl = gridContainer.dataset.apiUrl;

            var STATUS_LABELS = { todo: 'To Do', in_progress: 'In Progress', completed: 'Completed' };
            var PRIORITY_LABELS = { low: 'Low', medium: 'Medium', high: 'High' };
            var PRIORITY_RANK = { low: 1, medium: 2, high: 3 };
            var STATUS_RANK = { todo: 1, in_progress: 2, completed: 3 };
            var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            var searchInput = document.getElementById('search');
            var statusSelect = document.getElementById('status');
            var prioritySelect = document.getElementById('priority');
            var dueSelect = document.getElementById('due');
            var sortSelect = document.getElementById('sort');
            var directionSelect = document.getElementById('direction');
            var resetButton = document.getElementById('filters-reset');
            var SORT_COL_IDS = { created_at: 'created_label', due_date: 'due_date_label', priority: 'priority_label', title: 'title', status: 'status_label' };

            function formatDateOnly(value) {
                if (!value) {
                    return '—';
                }
                var parts = value.split('-');
                var date = new Date(Date.UTC(+parts[0], +parts[1] - 1, +parts[2]));
                return MONTHS[date.getUTCMonth()] + ' ' + String(date.getUTCDate()).padStart(2, '0') + ', ' + date.getUTCFullYear();
            }

            function formatDateTime(value) {
                if (!value) {
                    return '—';
                }
                var date = new Date(value);
                return MONTHS[date.getMonth()] + ' ' + String(date.getDate()).padStart(2, '0') + ', ' + date.getFullYear();
            }

            // Maps a raw task from the JSON API into the row shape the grid renderers expect.
            function toRow(task) {
                return {
                    id: task.id,
                    title: task.title,
                    status: task.status,
                    status_label: STATUS_LABELS[task.status] || task.status,
                    priority: task.priority,
                    priority_label: PRIORITY_LABELS[task.priority] || task.priority,
                    is_overdue: task.is_overdue,
                    due_date_raw: task.due_date,
                    due_date_label: formatDateOnly(task.due_date),
                    created_at_raw: task.created_at,
                    created_label: formatDateTime(task.created_at),
                    description: task.description || '',
                    priority_rank: PRIORITY_RANK[task.priority] || 0,
                    status_rank: STATUS_RANK[task.status] || 0,
                    show_url: '/tasks/' + task.id,
                    edit_url: '/tasks/' + task.id + '/edit',
                    is_completed: task.status === 'completed',
                    complete_api_url: '/api/tasks/' + task.id + '/complete',
                    reopen_api_url: '/api/tasks/' + task.id + '/reopen',
                    delete_api_url: '/api/tasks/' + task.id,
                };
            }

            // Replicates the server's due-date filter categories (Task::scopeDueFilter) on the client.
            function matchesDueFilter(row, due) {
                if (due === 'all' || !due) {
                    return true;
                }
                if (due === 'no_due_date') {
                    return !row.due_date_raw;
                }
                if (!row.due_date_raw) {
                    return false;
                }
                if (due === 'overdue') {
                    return row.is_overdue;
                }
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                if (due === 'today') {
                    var todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
                    return row.due_date_raw === todayStr;
                }
                if (due === 'this_week') {
                    var dayOfWeek = today.getDay();
                    var mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
                    var startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() + mondayOffset);
                    var endOfWeek = new Date(startOfWeek);
                    endOfWeek.setDate(startOfWeek.getDate() + 6);
                    var dueDate = new Date(row.due_date_raw + 'T00:00:00');
                    return dueDate >= startOfWeek && dueDate <= endOfWeek;
                }
                return true;
            }

            function doesRowPassFilters(row) {
                var search = (searchInput.value || '').trim().toLowerCase();
                if (search && row.title.toLowerCase().indexOf(search) === -1 && row.description.toLowerCase().indexOf(search) === -1) {
                    return false;
                }
                if (statusSelect.value !== 'all' && row.status !== statusSelect.value) {
                    return false;
                }
                if (prioritySelect.value !== 'all' && row.priority !== prioritySelect.value) {
                    return false;
                }
                if (!matchesDueFilter(row, dueSelect.value)) {
                    return false;
                }
                return true;
            }

            function applySort() {
                var colId = SORT_COL_IDS[sortSelect.value] || 'created_label';
                gridApi.applyColumnState({
                    state: [{ colId: colId, sort: directionSelect.value === 'asc' ? 'asc' : 'desc' }],
                    defaultState: { sort: null },
                });
            }

            function badge(className, text) {
                var span = document.createElement('span');
                span.className = className;
                span.textContent = text;
                return span;
            }

            function apiAction(url, method, redirectUrl) {
                fetch(url, { method: method, headers: { Accept: 'application/json' } })
                    .then(function (response) {
                        if (!response.ok) {
                            return response.json().then(function (body) {
                                throw new Error(body.message || 'Request failed with status ' + response.status);
                            });
                        }
                        if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else {
                            window.location.reload();
                        }
                    })
                    .catch(function (error) {
                        window.alert(error.message);
                    });
            }

            var columnDefs = [
                {
                    headerName: 'Task',
                    field: 'title',
                    flex: 2,
                    minWidth: 180,
                    cellRenderer: function (params) {
                        var link = document.createElement('a');
                        link.href = params.data.show_url;
                        link.className = 'task-title-link';
                        link.textContent = params.value;
                        return link;
                    },
                },
                {
                    headerName: 'Priority',
                    field: 'priority_label',
                    width: 120,
                    comparator: function (a, b, nodeA, nodeB) {
                        return nodeA.data.priority_rank - nodeB.data.priority_rank;
                    },
                    cellRenderer: function (params) {
                        return badge(' badge-priority-' + params.data.priority, params.value);
                    },
                },
                {
                    headerName: 'Status',
                    field: 'status_label',
                    width: 170,
                    comparator: function (a, b, nodeA, nodeB) {
                        return nodeA.data.status_rank - nodeB.data.status_rank;
                    },
                    cellRenderer: function (params) {
                        var wrapper = document.createElement('span');
                        wrapper.appendChild(badge('badge-status-' + params.data.status, params.value));
                        if (params.data.is_overdue) {
                            var overdue = badge('badge-overdue', 'Overdue');
                            overdue.style.marginLeft = '0.35rem';
                            wrapper.appendChild(overdue);
                        }
                        return wrapper;
                    },
                },
                {
                    headerName: 'Due Date',
                    field: 'due_date_label',
                    width: 130,
                    comparator: function (a, b, nodeA, nodeB) {
                        var rawA = nodeA.data.due_date_raw || '';
                        var rawB = nodeB.data.due_date_raw || '';
                        return rawA < rawB ? -1 : rawA > rawB ? 1 : 0;
                    },
                },
                {
                    headerName: 'Created',
                    field: 'created_label',
                    width: 130,
                    comparator: function (a, b, nodeA, nodeB) {
                        var rawA = nodeA.data.created_at_raw || '';
                        var rawB = nodeB.data.created_at_raw || '';
                        return rawA < rawB ? -1 : rawA > rawB ? 1 : 0;
                    },
                },
                {
                    headerName: 'Actions',
                    field: 'id',
                    width: 190,
                    sortable: false,
                    filter: false,
                    cellRenderer: function (params) {
                        var wrapper = document.createElement('div');
                        wrapper.className = 'row-actions';

                        var edit = document.createElement('a');
                        edit.href = params.data.edit_url;
                        edit.className = 'btn btn-sm btn-secondary';
                        edit.textContent = 'Edit';
                        wrapper.appendChild(edit);

                        var toggle = document.createElement('button');
                        toggle.type = 'button';
                        if (params.data.is_completed) {
                            toggle.className = 'btn btn-sm btn-icon btn-secondary';
                            toggle.setAttribute('aria-label', 'Reopen task');
                            toggle.title = 'Reopen';
                            toggle.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"></path><polyline points="3 4 3 9 8 9"></polyline></svg>';
                            toggle.addEventListener('click', function () {
                                apiAction(params.data.reopen_api_url, 'PATCH');
                            });
                        } else {
                            toggle.className = 'btn btn-sm btn-icon btn-success';
                            toggle.setAttribute('aria-label', 'Mark task as completed');
                            toggle.title = 'Complete';
                            toggle.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                            toggle.addEventListener('click', function () {
                                apiAction(params.data.complete_api_url, 'PATCH');
                            });
                        }
                        wrapper.appendChild(toggle);

                        var del = document.createElement('button');
                        del.type = 'button';
                        del.className = 'btn btn-sm btn-icon btn-danger';
                        del.setAttribute('aria-label', 'Delete task');
                        del.title = 'Delete';
                        del.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>';
                        del.addEventListener('click', function () {
                            if (window.confirm('Are you sure you want to delete this task?')) {
                                apiAction(params.data.delete_api_url, 'DELETE');
                            }
                        });
                        wrapper.appendChild(del);

                        return wrapper;
                    },
                },
            ];

            var gridApi = agGrid.createGrid(gridContainer, {
                columnDefs: columnDefs,
                rowData: [],
                defaultColDef: { resizable: true, sortable: true },
                suppressCellFocus: true,
                pagination: true,
                paginationPageSize: 10,
                paginationPageSizeSelector: [10, 20, 50],
                isExternalFilterPresent: function () {
                    return true;
                },
                doesExternalFilterPass: function (node) {
                    return doesRowPassFilters(node.data);
                },
                overlayLoadingTemplate: '<span class="ag-overlay-loading-center">Loading tasks...</span>',
                overlayNoRowsTemplate: '<span class="ag-overlay-loading-center">No tasks match your filters.</span>',
            });

            gridApi.showLoadingOverlay();
            applySort();

            ['input', 'change'].forEach(function (evt) {
                searchInput.addEventListener(evt, function () {
                    gridApi.onFilterChanged();
                });
            });
            [statusSelect, prioritySelect, dueSelect].forEach(function (select) {
                select.addEventListener('change', function () {
                    gridApi.onFilterChanged();
                });
            });
            [sortSelect, directionSelect].forEach(function (select) {
                select.addEventListener('change', applySort);
            });
            resetButton.addEventListener('click', function () {
                searchInput.value = '';
                statusSelect.value = 'all';
                prioritySelect.value = 'all';
                dueSelect.value = 'all';
                sortSelect.value = 'created_at';
                directionSelect.value = 'desc';
                applySort();
                gridApi.onFilterChanged();
            });

            // Render the grid entirely from the JSON API; AG Grid then owns filtering, sorting and paging.
            fetch(apiUrl, { headers: { Accept: 'application/json' } })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to load tasks (status ' + response.status + ').');
                    }
                    return response.json();
                })
                .then(function (json) {
                    var rows = (json.data || []).map(toRow);
                    gridApi.setGridOption('rowData', rows);
                    gridApi.onFilterChanged();
                })
                .catch(function (error) {
                    gridApi.setGridOption('overlayNoRowsTemplate', '<span class="ag-overlay-loading-center">' + error.message + '</span>');
                    gridApi.showNoRowsOverlay();
                });
        })();
    </script>
@endpush
