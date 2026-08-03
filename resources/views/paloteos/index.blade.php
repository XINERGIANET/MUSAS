@extends('template.index')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Cuadre de Cuentas - Paloteo</h4>
                        <span class="badge bg-primary">{{ now()->format('d/m/Y') }}</span>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Turno Mañana -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white py-2">
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <i class="fas fa-sun me-2"></i> Turno Mañana
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="morningForm">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted">Efectivo Inicial</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control" step="0.01" min="0" id="morningInitialCash">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted">Ventas</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control" step="0.01" min="0" id="morningSales">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted">Gastos</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control" step="0.01" min="0" id="morningExpenses">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted">Depósitos</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control" step="0.01" min="0" id="morningDeposits">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <hr class="my-2">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label small text-muted">Efectivo Final</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control fw-bold" step="0.01" min="0" id="morningFinalCash" readonly>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <button type="button" class="btn btn-primary w-100" id="saveMorning">
                                                    <i class="fas fa-save me-2"></i> Guardar Mañana
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Turno Noche -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-dark">
                                <div class="card-header bg-dark text-white py-2">
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <i class="fas fa-moon me-2"></i> Turno Noche
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="nightForm">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted">Efectivo Inicial</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control" step="0.01" min="0" id="nightInitialCash">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted">Ventas</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control" step="0.01" min="0" id="nightSales">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted">Gastos</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control" step="0.01" min="0" id="nightExpenses">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted">Depósitos</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control" step="0.01" min="0" id="nightDeposits">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <hr class="my-2">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label small text-muted">Efectivo Final</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">S/</span>
                                                    <input type="number" class="form-control fw-bold" step="0.01" min="0" id="nightFinalCash" readonly>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <button type="button" class="btn btn-dark w-100" id="saveNight">
                                                    <i class="fas fa-save me-2"></i> Guardar Noche
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Función para calcular efectivo final
    const calculateFinalCash = (prefix) => {
        const initial = parseFloat($(`#${prefix}InitialCash`).val()) || 0;
        const sales = parseFloat($(`#${prefix}Sales`).val()) || 0;
        const expenses = parseFloat($(`#${prefix}Expenses`).val()) || 0;
        const deposits = parseFloat($(`#${prefix}Deposits`).val()) || 0;
        
        const final = initial + sales - expenses - deposits;
        $(`#${prefix}FinalCash`).val(final.toFixed(2));
    };

    // Eventos para cálculo automático
    $('[id*="InitialCash"], [id*="Sales"], [id*="Expenses"], [id*="Deposits"]').on('input', function() {
        const prefix = this.id.includes('morning') ? 'morning' : 'night';
        calculateFinalCash(prefix);
    });

    // Funcionalidad falsa de guardado
    $('#saveMorning').click(function() {
        const btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Guardando...');
        
        setTimeout(() => {
            btn.html('<i class="fas fa-save me-2"></i> Guardar Mañana');
            Swal.fire({
                icon: 'success',
                title: 'Turno Mañana',
                text: 'Datos guardados correctamente (simulación)',
                timer: 2000
            });
        }, 1500);
    });

    $('#saveNight').click(function() {
        const btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Guardando...');
        
        setTimeout(() => {
            btn.html('<i class="fas fa-save me-2"></i> Guardar Noche');
            Swal.fire({
                icon: 'success',
                title: 'Turno Noche',
                text: 'Datos guardados correctamente (simulación)',
                timer: 2000
            });
        }, 1500);
    });
});
</script>

<style>
.card {
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.card-header {
    border-radius: 0.5rem 0.5rem 0 0 !important;
}
.input-group-text {
    background-color: #f8f9fa;
    font-weight: 500;
}
.form-control:read-only {
    background-color: #f8f9fa;
}
</style>
@endsection