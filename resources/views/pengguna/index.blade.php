@extends('layouts.app')

@section('content')
<div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
  <div class="card-body px-4 py-3">
    <div class="row align-items-center">
      <div class="col-9">
        <h4 class="fw-semibold mb-8">Data Pengguna</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="/">Dashboard</a></li>
            <li class="breadcrumb-item" aria-current="page">God Mode</li>
            <li class="breadcrumb-item" aria-current="page">Pengguna</li>
          </ol>
        </nav>
      </div>
      <div class="col-3">
        <div class="text-center mb-n5">
          <i class="ti ti-users fs-8 text-info"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h5 class="card-title fw-semibold">Daftar Pengguna</h5>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="ti ti-plus"></i> Tambah Data
      </button>
    </div>

    <div class="table-responsive">
      <table id="dataTable" class="table border table-striped table-bordered text-nowrap">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Lengkap</th>
            <th>Username</th>
            <th>No HP</th>
            <th>Level</th>
            <th>Lembaga / Jabatan</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @php
            $levelMap = [
                1 => 'Sekolah/Madrasah',
                2 => 'Staf Bidang',
                3 => 'Kasubag',
                4 => 'Kabag',
                5 => 'KTU',
                6 => 'Kabid',
                7 => 'Super Admin'
            ];
          @endphp
          @foreach($penggunas as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->username }}</td>
            <td>{{ $item->no_hp ?: '-' }}</td>
            <td>{{ $levelMap[$item->level] ?? 'Unknown' }}</td>
            <td>
              @if($item->level == 1 && $item->lembaga)
                <span class="badge bg-primary">{{ $item->lembaga->nama_lembaga }}</span>
              @elseif($item->level >= 2 && $item->level <= 6 && $item->jabatan)
                <span class="badge bg-secondary">{{ $item->jabatan->nama_jabatan }}</span>
              @elseif($item->level == 7)
                <span class="badge bg-dark">Semua Akses</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              @if($item->status == 1)
                <span class="badge bg-success">Aktif</span>
              @else
                <span class="badge bg-danger">Blokir</span>
              @endif
            </td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id_user }}">
                <i class="ti ti-edit"></i> Edit
              </button>
              <form action="{{ route('pengguna.destroy', $item->id_user) }}" method="POST" class="d-inline form-delete">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-danger btn-delete">
                  <i class="ti ti-trash"></i> Hapus
                </button>
              </form>
            </td>
          </tr>

          <!-- Modal Edit -->
          <div class="modal fade" id="modalEdit{{ $item->id_user }}" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <form action="{{ route('pengguna.update', $item->id_user) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel">Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" value="{{ $item->name }}" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" value="{{ $item->username }}" required>
                      </div>
                      <div class="col-md-12 mb-3">
                        <label class="form-label">No HP</label>
                        <input type="text" class="form-control" name="no_hp" value="{{ $item->no_hp }}">
                      </div>
                      <div class="col-md-12 mb-3">
                        <label class="form-label">Password <small class="text-danger">(Kosongkan jika tidak ingin diubah)</small></label>
                        <input type="password" class="form-control" name="password" minlength="6">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Level Hak Akses</label>
                        <select class="form-select level-select" name="level" data-target="edit{{ $item->id_user }}" required>
                          <option value="">-- Pilih Level --</option>
                          @foreach($levelMap as $key => $val)
                            <option value="{{ $key }}" {{ $item->level == $key ? 'selected' : '' }}>{{ $val }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Status Akun</label>
                        <select class="form-select" name="status" required>
                          <option value="1" {{ $item->status == 1 ? 'selected' : '' }}>Aktif</option>
                          <option value="0" {{ $item->status == 0 ? 'selected' : '' }}>Blokir / Nonaktif</option>
                        </select>
                      </div>

                      <div class="col-md-12 mb-3 lembaga-container edit{{ $item->id_user }} {{ $item->level == 1 ? '' : 'd-none' }}">
                        <label class="form-label">Pilih Lembaga (Khusus Level 1)</label>
                        <select class="form-select lembaga-select edit{{ $item->id_user }}" name="id_lembaga" {{ $item->level == 1 ? 'required' : '' }}>
                          <option value="">-- Pilih Lembaga --</option>
                          @foreach($lembagas as $lbg)
                            <option value="{{ $lbg->id_lembaga }}" {{ $item->id_lembaga == $lbg->id_lembaga ? 'selected' : '' }}>{{ $lbg->nama_lembaga }}</option>
                          @endforeach
                        </select>
                      </div>

                      <div class="col-md-12 mb-3 jabatan-container edit{{ $item->id_user }} {{ ($item->level >= 2 && $item->level <= 6) ? '' : 'd-none' }}">
                        <label class="form-label">Pilih Jabatan (Khusus Level 2-6)</label>
                        <select class="form-select jabatan-select edit{{ $item->id_user }}" name="id_jabatan" {{ ($item->level >= 2 && $item->level <= 6) ? 'required' : '' }}>
                          <option value="">-- Pilih Jabatan --</option>
                          @foreach($jabatans as $jbtn)
                            <option value="{{ $jbtn->id_jabatan }}" {{ $item->id_jabatan == $jbtn->id_jabatan ? 'selected' : '' }}>{{ $jbtn->nama_jabatan }}</option>
                          @endforeach
                        </select>
                      </div>

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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('pengguna.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahLabel">Tambah Pengguna</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" name="username" required>
            </div>
            <div class="col-md-12 mb-3">
              <label class="form-label">No HP</label>
              <input type="text" class="form-control" name="no_hp">
            </div>
            <div class="col-md-12 mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required minlength="6">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Level Hak Akses</label>
              <select class="form-select level-select" name="level" data-target="tambah" required>
                <option value="">-- Pilih Level --</option>
                @foreach($levelMap as $key => $val)
                  <option value="{{ $key }}">{{ $val }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Status Akun</label>
              <select class="form-select" name="status" required>
                <option value="1">Aktif</option>
                <option value="0">Blokir / Nonaktif</option>
              </select>
            </div>

            <div class="col-md-12 mb-3 lembaga-container tambah d-none">
              <label class="form-label">Pilih Lembaga (Khusus Level 1)</label>
              <select class="form-select lembaga-select tambah" name="id_lembaga">
                <option value="">-- Pilih Lembaga --</option>
                @foreach($lembagas as $lbg)
                  <option value="{{ $lbg->id_lembaga }}">{{ $lbg->nama_lembaga }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-12 mb-3 jabatan-container tambah d-none">
              <label class="form-label">Pilih Jabatan (Khusus Level 2-6)</label>
              <select class="form-select jabatan-select tambah" name="id_jabatan">
                <option value="">-- Pilih Jabatan --</option>
                @foreach($jabatans as $jbtn)
                  <option value="{{ $jbtn->id_jabatan }}">{{ $jbtn->nama_jabatan }}</option>
                @endforeach
              </select>
            </div>

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
        text: "Data pengguna ini akan dihapus permanen!",
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

    // Dynamic dropdown logic
    $('.level-select').on('change', function() {
        let level = $(this).val();
        let target = $(this).data('target');
        
        let lembagaContainer = $('.lembaga-container.' + target);
        let jabatanContainer = $('.jabatan-container.' + target);
        let lembagaSelect = $('.lembaga-select.' + target);
        let jabatanSelect = $('.jabatan-select.' + target);

        if (level == 1) {
            lembagaContainer.removeClass('d-none');
            lembagaSelect.prop('required', true);
            
            jabatanContainer.addClass('d-none');
            jabatanSelect.prop('required', false);
            jabatanSelect.val('');
        } else if (level >= 2 && level <= 6) {
            jabatanContainer.removeClass('d-none');
            jabatanSelect.prop('required', true);
            
            lembagaContainer.addClass('d-none');
            lembagaSelect.prop('required', false);
            lembagaSelect.val('');
        } else {
            lembagaContainer.addClass('d-none');
            lembagaSelect.prop('required', false);
            lembagaSelect.val('');

            jabatanContainer.addClass('d-none');
            jabatanSelect.prop('required', false);
            jabatanSelect.val('');
        }
    });
  });
</script>
@endpush
