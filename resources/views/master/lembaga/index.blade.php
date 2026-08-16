@extends('layouts.app')

@section('content')
<div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
  <div class="card-body px-4 py-3">
    <div class="row align-items-center">
      <div class="col-9">
        <h4 class="fw-semibold mb-8">Data Lembaga</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="/">Dashboard</a></li>
            <li class="breadcrumb-item" aria-current="page">Master Data</li>
            <li class="breadcrumb-item" aria-current="page">Lembaga</li>
          </ol>
        </nav>
      </div>
      <div class="col-3">
        <div class="text-center mb-n5">
          <i class="ti ti-building fs-8 text-info"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h5 class="card-title fw-semibold">Daftar Lembaga</h5>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="ti ti-plus"></i> Tambah Data
      </button>
    </div>

    <div class="table-responsive">
      <table id="dataTable" class="table border table-striped table-bordered text-nowrap">
        <thead>
          <tr>
            <th>No</th>
            <th>ID Lembaga</th>
            <th>Nama Lembaga</th>
            <th>Singkatan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($lembagas as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->id_lembaga }}</td>
            <td>{{ $item->nama_lembaga }}</td>
            <td>{{ $item->singkatan_lembaga }}</td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id_lembaga }}">
                <i class="ti ti-edit"></i> Edit
              </button>
              <form action="{{ route('master.lembaga.destroy', $item->id_lembaga) }}" method="POST" class="d-inline form-delete">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-danger btn-delete">
                  <i class="ti ti-trash"></i> Hapus
                </button>
              </form>
            </td>
          </tr>

          <!-- Modal Edit -->
          <div class="modal fade" id="modalEdit{{ $item->id_lembaga }}" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="{{ route('master.lembaga.update', $item->id_lembaga) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel">Edit Lembaga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">ID Lembaga</label>
                      <input type="text" class="form-control" value="{{ $item->id_lembaga }}" readonly>
                      <small class="text-muted">ID Lembaga tidak dapat diubah.</small>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nama Lembaga</label>
                      <input type="text" class="form-control" name="nama_lembaga" value="{{ $item->nama_lembaga }}" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Singkatan</label>
                      <input type="text" class="form-control" name="singkatan_lembaga" value="{{ $item->singkatan_lembaga }}" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('master.lembaga.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahLabel">Tambah Lembaga</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">ID Lembaga</label>
            <input type="text" class="form-control" name="id_lembaga" required maxlength="10">
          </div>
          <div class="mb-3">
            <label class="form-label">Nama Lembaga</label>
            <input type="text" class="form-control" name="nama_lembaga" required maxlength="50">
          </div>
          <div class="mb-3">
            <label class="form-label">Singkatan</label>
            <input type="text" class="form-control" name="singkatan_lembaga" required maxlength="15">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().destroy();
    }
    $('#dataTable').DataTable({
      "language": {
        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
      }
    });

    $('.btn-delete').on('click', function(e) {
      e.preventDefault();
      let form = $(this).closest('form');
      Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data lembaga ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      })
    });
  });
</script>
@endpush
