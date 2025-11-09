<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CSV Upload - Yoprint Uploader</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .upload-form {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            background: #f9fafb;
        }
        
        .upload-form:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .upload-form.dragover {
            border-color: #667eea;
            background: #e8edff;
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-label {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .file-label:hover {
            background: #5568d3;
        }
        
        .file-name {
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }
        
        .btn-submit {
            margin-top: 20px;
            padding: 12px 32px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-submit:hover:not(:disabled) {
            background: #059669;
        }
        
        .btn-submit:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        
        .uploads-list {
            margin-top: 30px;
        }
        
        .uploads-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px;
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 2px solid #e5e7eb;
        }
        
        td {
            padding: 16px 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }
        
        tr:hover {
            background: #f9fafb;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .refresh-indicator {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 10px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>CSV File Upload</h1>
            <p class="subtitle">Upload your CSV file to process products data</p>
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('info'))
                <div class="alert alert-info">
                    {{ session('info') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="upload-form" id="uploadArea">
                    <div class="file-input-wrapper">
                        <input type="file" name="csv_file" id="csv_file" class="file-input" accept=".csv,.txt" required>
                        <label for="csv_file" class="file-label">Choose CSV File</label>
                    </div>
                    <div class="file-name" id="fileName"></div>
                    <button type="submit" class="btn-submit" id="submitBtn" disabled>Upload & Process</button>
                </div>
            </form>
        </div>
        
        <div class="card uploads-list">
            <div class="uploads-header">
                <h2>Recent Uploads</h2>
                <div class="refresh-indicator" id="refreshIndicator">Last updated: <span id="lastUpdate">-</span></div>
            </div>
            
            <div id="uploadsTableContainer">
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Status</th>
                            <th>Upload Time</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody id="uploadsTableBody">
                        @foreach($uploads as $upload)
                            <tr data-upload-id="{{ $upload->id }}">
                                <td>{{ $upload->original_filename ?? $upload->filename }}</td>
                                <td>
                                    <span class="status-badge status-{{ $upload->status }}">
                                        {{ $upload->status }}
                                    </span>
                                </td>
                                <td>{{ $upload->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $upload->updated_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                @if($uploads->isEmpty())
                    <p style="text-align: center; padding: 40px; color: #9ca3af;">No uploads yet. Upload your first CSV file above.</p>
                @endif
            </div>
        </div>
    </div>
    
    <script>
        // File input handling
        const fileInput = document.getElementById('csv_file');
        const fileName = document.getElementById('fileName');
        const submitBtn = document.getElementById('submitBtn');
        const uploadArea = document.getElementById('uploadArea');
        const uploadForm = document.getElementById('uploadForm');
        
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                fileName.textContent = 'Selected: ' + e.target.files[0].name;
                submitBtn.disabled = false;
            } else {
                fileName.textContent = '';
                submitBtn.disabled = true;
            }
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                fileName.textContent = 'Selected: ' + e.dataTransfer.files[0].name;
                submitBtn.disabled = false;
            }
        });
        
        // Form submission
        uploadForm.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
        });
        
        // Real-time polling for upload status
        let pollingInterval;
        const statusColors = {
            'pending': 'status-pending',
            'processing': 'status-processing',
            'completed': 'status-completed',
            'failed': 'status-failed'
        };
        
        function updateUploadsTable() {
            fetch('/api/uploads')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('uploadsTableBody');
                    
                    if (data.data && data.data.length > 0) {
                        tbody.innerHTML = data.data.map(upload => `
                            <tr data-upload-id="${upload.id}">
                                <td>${upload.filename}</td>
                                <td>
                                    <span class="status-badge ${statusColors[upload.status]}">
                                        ${upload.status}
                                    </span>
                                </td>
                                <td>${upload.created_at}</td>
                                <td>${upload.upload_time}</td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 40px; color: #9ca3af;">No uploads yet.</td></tr>';
                    }
                    
                    // Update last refresh time
                    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
                })
                .catch(error => {
                    console.error('Error fetching uploads:', error);
                });
        }
        
        // Start polling every 3 seconds
        function startPolling() {
            updateUploadsTable();
            pollingInterval = setInterval(updateUploadsTable, 3000);
        }
        
        // Stop polling when page is hidden
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(pollingInterval);
            } else {
                startPolling();
            }
        });
        
        // Start polling on page load
        startPolling();
    </script>
</body>
</html>