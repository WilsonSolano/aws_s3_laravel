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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined') {
        $(function () {
            let selectedFiles = [];
            console.log('jQuery cargado y listo (desde stack)');
        });
    } else {
        console.error('$ no está definido aún');
    }

    let selectedFiles = [];

    // Eventos
    $('#selectBtn').click(() => $('#fileInput').click());
    $('#fileInput').change(handleFileSelect);
    $('#clearBtn').click(clearFiles);
    $('#uploadBtn').click(uploadFiles);
    
    // Drag and drop
    $('#dropZone')
        .on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('border-success bg-success-subtle').removeClass('border-primary');
        })
        .on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('border-success bg-success-subtle').addClass('border-primary');
        })
        .on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('border-success bg-success-subtle').addClass('border-primary');
            const files = Array.from(e.originalEvent.dataTransfer.files);
            processFiles(files);
        });

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
        const fileItems = $('#fileItems');
        fileItems.empty();
        
        selectedFiles.forEach((file, index) => {
            const fileIcon = file.type.includes('json') ? 'bi-filetype-json text-success' : 'bi-filetype-pdf text-danger';
            const fileSize = (file.size / 1024).toFixed(1);
            
            const fileItem = $(`
                <div class="card border-0 bg-light rounded-3 p-3 mb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi ${fileIcon} fs-4 me-3"></i>
                            <div>
                                <div class="fw-semibold">${file.name}</div>
                                <small class="text-muted">${fileSize} KB</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle remove-file" data-index="${index}">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            `);
            
            fileItems.append(fileItem);
        });

        $('#fileList').removeClass('d-none');
        $('#actionButtons').removeClass('d-none');
    }

    // Event delegation para botones de eliminar
    $(document).on('click', '.remove-file', function() {
        const index = $(this).data('index');
        selectedFiles.splice(index, 1);
        if (selectedFiles.length > 0) {
            displayFiles();
        } else {
            clearFiles();
        }
    });

    function clearFiles() {
        selectedFiles = [];
        $('#fileItems').empty();
        $('#fileList').addClass('d-none');
        $('#actionButtons').addClass('d-none');
        $('#fileInput').val('');
    }

    function uploadFiles() {
        if (selectedFiles.length === 0) {
            showAlert('No hay archivos para subir.', 'warning');
            return;
        }

        // Preparar FormData
        const formData = new FormData();
        selectedFiles.forEach(file => {
            formData.append('archivos[]', file);
        });
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        // UI de carga
        const uploadBtn = $('#uploadBtn');
        uploadBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Subiendo...').prop('disabled', true);

        // Realizar petición AJAX
        $.ajax({
            url: "{{ route('archivos.procesar-carga') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response.success) {
                    // Mostrar mensaje de éxito
                    let alertType = response.tipo || 'success';
                    showAlert(response.mensaje, alertType);
                    
                    // Si hay errores, mostrarlos también
                    if (response.errores && response.errores.length > 0) {
                        response.errores.forEach(error => {
                            showAlert(`Error en ${error.archivo}: ${error.error}`, 'danger');
                        });
                    }
                    
                    // Limpiar archivos seleccionados solo si hay éxito
                    clearFiles();
                    
                    // Log de archivos subidos para debug
                    if (response.archivos_subidos && response.archivos_subidos.length > 0) {
                        console.log('Archivos subidos exitosamente:', response.archivos_subidos);
                    }
                } else {
                    showAlert(response.mensaje || 'Error desconocido al subir los archivos.', 'danger');
                }
            },
            error: function(xhr) {
                console.error('Error en la petición:', xhr);
                let errorMessage = 'Error al subir los archivos.';
                
                if (xhr.status === 422) {
                    // Errores de validación
                    const errors = xhr.responseJSON?.errors || {};
                    const errorMessages = Object.values(errors).flat();
                    errorMessage = errorMessages.length > 0 ? errorMessages.join(' ') : errorMessage;
                } else if (xhr.responseJSON?.mensaje) {
                    errorMessage = xhr.responseJSON.mensaje;
                } else if (xhr.status === 500) {
                    errorMessage = 'Error interno del servidor. Intente nuevamente.';
                } else if (xhr.status === 413) {
                    errorMessage = 'Los archivos son demasiado grandes.';
                }
                
                showAlert(errorMessage, 'danger');
            },
            complete: function() {
                uploadBtn.html('<i class="bi bi-upload me-2"></i>Subir Archivos').prop('disabled', false);
            }
        });
    }

    function showAlert(message, type) {
        // Remover alertas existentes del mismo tipo para evitar spam
        $(`.custom-alert.alert-${type}`).remove();
        
        const alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 custom-alert" style="z-index: 9999; max-width: 500px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
});
</script>
@endpush