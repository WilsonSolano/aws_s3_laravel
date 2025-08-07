@extends('layout.app')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <!-- Header -->
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-cloud-upload-fill text-primary fs-1"></i>
                </div>
            </div>

            <!-- Card Principal -->
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <!-- Zona de Drop -->
                    <div id="dropZone" class="border-2 border-dashed border-primary rounded-4 p-5 text-center position-relative bg-light bg-opacity-50 transition-all" style="min-height: 300px; transition: all 0.3s ease;">
                        <div class="d-flex flex-column align-items-center justify-content-center h-100">
                            <div class="mb-4">
                                <i class="bi bi-file-earmark-arrow-up text-primary" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="fw-semibold text-dark mb-3">Arrastra y suelta tus archivos aquí</h4>
                            <p class="text-muted mb-4">o haz clic para seleccionar archivos</p>
                            
                            <!-- Input file oculto -->
                            <input type="file" id="fileInput" class="d-none" accept=".json,.pdf,application/json,application/pdf" multiple>
                            
                            <!-- Botón de selección -->
                            <button type="button" class="btn btn-primary btn-lg px-4 py-3 rounded-pill fw-semibold" id="selectBtn">
                                <i class="bi bi-folder2-open me-2"></i>
                                Seleccionar Archivos
                            </button>
                            
                            <!-- Formatos aceptados -->
                            <div class="mt-4">
                                <small class="text-muted">Formatos aceptados:</small>
                                <div class="d-flex gap-3 justify-content-center mt-2">
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                        <i class="bi bi-filetype-json me-1"></i>JSON
                                    </span>
                                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                                        <i class="bi bi-filetype-pdf me-1"></i>PDF
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de archivos seleccionados -->
                    <div id="fileList" class="mt-4 d-none">
                        <h5 class="fw-semibold mb-3">
                            <i class="bi bi-files text-primary me-2"></i>
                            Archivos Seleccionados
                        </h5>
                        <div id="fileItems" class="d-flex flex-column gap-2"></div>
                    </div>

                    <!-- Botones de acción -->
                    <div id="actionButtons" class="mt-4 d-none">
                        <div class="d-flex gap-3 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-pill" id="clearBtn">
                                <i class="bi bi-x-circle me-2"></i>
                                Limpiar
                            </button>
                            <button type="button" class="btn btn-success px-4 py-2 rounded-pill" id="uploadBtn">
                                <i class="bi bi-upload me-2"></i>
                                Subir Archivos
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para la funcionalidad -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const selectBtn = document.getElementById('selectBtn');
    const fileList = document.getElementById('fileList');
    const fileItems = document.getElementById('fileItems');
    const actionButtons = document.getElementById('actionButtons');
    const clearBtn = document.getElementById('clearBtn');
    const uploadBtn = document.getElementById('uploadBtn');
    
    let selectedFiles = [];

    // Eventos del botón seleccionar
    selectBtn.addEventListener('click', () => fileInput.click());
    
    // Eventos de drag and drop
    dropZone.addEventListener('dragover', handleDragOver);
    dropZone.addEventListener('dragleave', handleDragLeave);
    dropZone.addEventListener('drop', handleDrop);
    
    // Evento de selección de archivos
    fileInput.addEventListener('change', handleFileSelect);
    
    // Eventos de botones
    clearBtn.addEventListener('click', clearFiles);
    uploadBtn.addEventListener('click', uploadFiles);

    function handleDragOver(e) {
        e.preventDefault();
        dropZone.classList.add('border-success', 'bg-success-subtle');
        dropZone.classList.remove('border-primary');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        dropZone.classList.remove('border-success', 'bg-success-subtle');
        dropZone.classList.add('border-primary');
    }

    function handleDrop(e) {
        e.preventDefault();
        dropZone.classList.remove('border-success', 'bg-success-subtle');
        dropZone.classList.add('border-primary');
        
        const files = Array.from(e.dataTransfer.files);
        processFiles(files);
    }

    function handleFileSelect(e) {
        const files = Array.from(e.target.files);
        processFiles(files);
    }

    function processFiles(files) {
        const validFiles = files.filter(file => {
            const validTypes = ['application/json', 'application/pdf', 'text/json'];
            const validExtensions = ['.json', '.pdf'];
            return validTypes.includes(file.type) || 
                   validExtensions.some(ext => file.name.toLowerCase().endsWith(ext));
        });

        if (validFiles.length > 0) {
            selectedFiles = [...selectedFiles, ...validFiles];
            displayFiles();
        }

        if (files.length > validFiles.length) {
            showAlert('Algunos archivos no son válidos. Solo se aceptan archivos JSON y PDF.', 'warning');
        }
    }

    function displayFiles() {
        fileItems.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'card border-0 bg-light rounded-3 p-3';
            
            const fileIcon = file.type.includes('json') ? 'bi-filetype-json text-success' : 'bi-filetype-pdf text-danger';
            const fileSize = (file.size / 1024).toFixed(1);
            
            fileItem.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi ${fileIcon} fs-4 me-3"></i>
                        <div>
                            <div class="fw-semibold">${file.name}</div>
                            <small class="text-muted">${fileSize} KB</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" onclick="removeFile(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
            
            fileItems.appendChild(fileItem);
        });

        fileList.classList.remove('d-none');
        actionButtons.classList.remove('d-none');
    }

    function clearFiles() {
        selectedFiles = [];
        fileItems.innerHTML = '';
        fileList.classList.add('d-none');
        actionButtons.classList.add('d-none');
        fileInput.value = '';
    }

    function uploadFiles() {
        if (selectedFiles.length === 0) {
            showAlert('No hay archivos para subir.', 'warning');
            return;
        }

        // Aquí implementarías la lógica de subida real
        showAlert(`Se subirán ${selectedFiles.length} archivo(s) correctamente.`, 'success');
        
        // Simular progreso de carga
        uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Subiendo...';
        uploadBtn.disabled = true;
        
        setTimeout(() => {
            uploadBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>¡Completado!';
            setTimeout(() => {
                uploadBtn.innerHTML = '<i class="bi bi-upload me-2"></i>Subir Archivos';
                uploadBtn.disabled = false;
                clearFiles();
            }, 2000);
        }, 2000);
    }

    // Función global para remover archivos
    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        if (selectedFiles.length > 0) {
            displayFiles();
        } else {
            clearFiles();
        }
    };

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 4000);
    }
});
</script>

@endsection