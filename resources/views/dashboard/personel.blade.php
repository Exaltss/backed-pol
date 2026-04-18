@extends('layouts.admin')
@section('title','Manajemen Personel')
@section('header-title','Manajemen Data Personel')

@section('content')
<style>
    .foto-avatar{width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;}
    .status-dot{width:8px;height:8px;border-radius:50%;display:inline-block;}
</style>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <span class="badge bg-primary fs-6">{{ $personnels->count() }} Personel Terdaftar</span>
    </div>
    <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-person-plus-fill me-2"></i>Tambah Personel
    </button>
</div>

<div class="card card-custom bg-white p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Foto</th>
                    <th>Nama & Pangkat</th>
                    <th>No HP / WhatsApp</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($personnels as $p)
                <tr>
                    <td>
                        @php
                            $fotoSrc = null;
                            if ($p->foto_profil) {
                                if (str_starts_with($p->foto_profil, 'http')) {
                                    $fotoSrc = $p->foto_profil;
                                } elseif (str_starts_with($p->foto_profil, 'profile_photos/')) {
                                    $fotoSrc = asset($p->foto_profil);
                                } else {
                                    $fotoSrc = asset('storage/' . $p->foto_profil);
                                }
                            }
                        @endphp
                        @if($fotoSrc)
                            <img src="{{ $fotoSrc }}" class="foto-avatar">
                        @else
                            <div class="foto-avatar bg-secondary d-flex align-items-center justify-content-center">
                                <i class="bi bi-person-fill text-white"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold">{{ $p->nama_lengkap }}</div>
                        <small class="text-muted">{{ $p->pangkat }}</small>
                    </td>
                    <td>
                        @if($p->nrp)
                        <a href="https://wa.me/{{ $p->nrp }}" target="_blank" class="text-success text-decoration-none fw-bold">
                            <i class="bi bi-whatsapp me-1"></i>{{ $p->nrp }}
                        </a>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td><code>{{ $p->user->username ?? '-' }}</code></td>
                    <td>
                        @php
                            $statusColors = ['online'=>'success','patroli'=>'primary','siaga'=>'warning','darurat'=>'danger','offline'=>'secondary'];
                            $sc = $statusColors[$p->status_aktif] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $sc }}">{{ strtoupper($p->status_aktif) }}</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning me-1"
                            onclick="openEdit({{ $p->id }},'{{ addslashes($p->nama_lengkap) }}','{{ $p->nrp }}','{{ $p->pangkat }}','{{ $p->user->username ?? '' }}')"
                            data-bs-toggle="modal" data-bs-target="#modalEdit">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <form action="{{ route('personel.destroy', $p->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus personel {{ $p->nama_lengkap }}? Semua datanya akan terhapus permanen!')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data personel.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Tambah Personel Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('personel.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Pangkat</label>
                            <select name="pangkat" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option>Bripda</option><option>Briptu</option><option>Brigpol</option>
                                <option>Aipda</option><option>Aiptu</option><option>Ipda</option>
                                <option>Iptu</option><option>AKP</option><option>Kompol</option><option>AKBP</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">No HP (format: 628xxx)</label>
                            <input type="text" name="no_hp" class="form-control" placeholder="628123456789" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Username Akun</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Foto Profil (opsional)</label>
                        <input type="file" name="foto_profil" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-fill me-2"></i>Edit Data Personel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Pangkat</label>
                            <select name="pangkat" id="edit_pangkat" class="form-select" required>
                                <option>Bripda</option><option>Briptu</option><option>Brigpol</option>
                                <option>Aipda</option><option>Aiptu</option><option>Ipda</option>
                                <option>Iptu</option><option>AKP</option><option>Kompol</option><option>AKBP</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">No HP (628xxx)</label>
                            <input type="text" name="no_hp" id="edit_nohp" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" class="form-control" minlength="6" placeholder="Isi jika ingin ganti password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ganti Foto (opsional)</label>
                        <input type="file" name="foto_profil" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-save me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEdit(id, nama, nohp, pangkat, username) {
    document.getElementById('edit_nama').value   = nama;
    document.getElementById('edit_nohp').value   = nohp;
    var sel = document.getElementById('edit_pangkat');
    for(var i=0;i<sel.options.length;i++){
        if(sel.options[i].value===pangkat) sel.selectedIndex=i;
    }
    document.getElementById('formEdit').action = '/manajemen-personel/' + id + '/update';
}
</script>
@endsection