<?php
require 'config.php';
$pesan = "";

// 1. Menangkap pesan dari URL (Redirect GET)
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses_tambah') {
        $pesan = "<div class='p-4 mb-8 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3' role='alert'>
                    <i data-lucide='check-circle-2' class='w-5 h-5'></i>
                    <div><span class='font-semibold'>Berhasil!</span> Dokumen tugas Anda telah tersimpan.</div>
                  </div>";
    } elseif ($_GET['status'] == 'sukses_hapus') {
        $pesan = "<div class='p-4 mb-8 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3' role='alert'>
                    <i data-lucide='check-circle-2' class='w-5 h-5'></i>
                    <div><span class='font-semibold'>Terhapus!</span> Data tugas berhasil dihapus.</div>
                  </div>";
    } elseif ($_GET['status'] == 'sukses_edit') {
        $pesan = "<div class='p-4 mb-8 text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-3' role='alert'>
                    <i data-lucide='check-circle-2' class='w-5 h-5'></i>
                    <div><span class='font-semibold'>Diperbarui!</span> Data tugas berhasil diubah.</div>
                  </div>";
    } elseif ($_GET['status'] == 'gagal') {
         $pesan = "<div class='p-4 mb-8 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3' role='alert'>
                    <i data-lucide='alert-circle' class='w-5 h-5'></i>
                    <div>Terjadi kesalahan saat memproses data.</div>
                  </div>";
    }
}

// 2. CREATE: Proses Upload Tugas Baru (Metode POST)
if (isset($_POST['submit_tambah'])) {
    $nim   = mysqli_real_escape_string($conn, $_POST['nim']);
    $nama  = mysqli_real_escape_string($conn, $_POST['nama']);
    $makul = mysqli_real_escape_string($conn, $_POST['mata_kuliah']);
    $judul = mysqli_real_escape_string($conn, $_POST['judul_tugas']);

    $file_name = $_FILES['file_tugas']['name'];
    $file_tmp  = $_FILES['file_tugas']['tmp_name'];
    $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed   = ['pdf', 'docx', 'zip'];

    if (in_array($ext, $allowed)) {
        $new_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
        $destination = 'uploads/' . $new_name;

        if (move_uploaded_file($file_tmp, $destination)) {
            $query = "INSERT INTO tugas (nim, nama_mahasiswa, mata_kuliah, judul_tugas, nama_file) 
                      VALUES ('$nim', '$nama', '$makul', '$judul', '$new_name')";
            if(mysqli_query($conn, $query)){
                header("Location: index.php?status=sukses_tambah");
                exit(); 
            } else {
                 header("Location: index.php?status=gagal");
                 exit();
            }
        } else {
             header("Location: index.php?status=gagal");
             exit();
        }
    } else {
        $pesan = "<div class='p-4 mb-8 text-sm text-orange-700 bg-orange-50 border border-orange-200 rounded-xl flex items-center gap-3' role='alert'>
                    <i data-lucide='alert-triangle' class='w-5 h-5'></i>
                    <div>Format file ditolak. Harap gunakan ekstensi .pdf, .docx, atau .zip.</div>
                  </div>";
    }
}

// 3. DELETE: Proses Hapus Data & File Fisik (Metode GET)
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    // Cari nama file lama untuk dihapus dari folder uploads
    $cari_file = mysqli_query($conn, "SELECT nama_file FROM tugas WHERE id = '$id_hapus'");
    if(mysqli_num_rows($cari_file) > 0){
        $data_file = mysqli_fetch_assoc($cari_file);
        $path_file = 'uploads/' . $data_file['nama_file'];
        
        // Hapus file fisik jika ada
        if (file_exists($path_file)) {
            unlink($path_file);
        }
        
        // Hapus data dari database
        mysqli_query($conn, "DELETE FROM tugas WHERE id = '$id_hapus'");
        header("Location: index.php?status=sukses_hapus");
        exit();
    }
}

// 4. UPDATE: Proses Edit Data (Metode POST)
if (isset($_POST['submit_edit'])) {
    $id_edit = mysqli_real_escape_string($conn, $_POST['id_edit']);
    $nim     = mysqli_real_escape_string($conn, $_POST['nim']);
    $nama    = mysqli_real_escape_string($conn, $_POST['nama']);
    $makul   = mysqli_real_escape_string($conn, $_POST['mata_kuliah']);
    $judul   = mysqli_real_escape_string($conn, $_POST['judul_tugas']);
    
    // Cek apakah user mengunggah file baru
    if($_FILES['file_tugas_baru']['name'] != ""){
        $file_name = $_FILES['file_tugas_baru']['name'];
        $file_tmp  = $_FILES['file_tugas_baru']['tmp_name'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['pdf', 'docx', 'zip'];
        
        if (in_array($ext, $allowed)) {
            $new_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
            $destination = 'uploads/' . $new_name;
            
            if (move_uploaded_file($file_tmp, $destination)) {
                // Hapus file lama
                $cari_lama = mysqli_query($conn, "SELECT nama_file FROM tugas WHERE id = '$id_edit'");
                $data_lama = mysqli_fetch_assoc($cari_lama);
                $path_lama = 'uploads/' . $data_lama['nama_file'];
                if (file_exists($path_lama)) { unlink($path_lama); }
                
                // Update database dengan file baru
                $query = "UPDATE tugas SET nim='$nim', nama_mahasiswa='$nama', mata_kuliah='$makul', judul_tugas='$judul', nama_file='$new_name' WHERE id='$id_edit'";
                mysqli_query($conn, $query);
                header("Location: index.php?status=sukses_edit");
                exit();
            }
        } else {
             header("Location: index.php?status=gagal");
             exit();
        }
    } else {
        // Jika tidak upload file baru, hanya update teks
        $query = "UPDATE tugas SET nim='$nim', nama_mahasiswa='$nama', mata_kuliah='$makul', judul_tugas='$judul' WHERE id='$id_edit'";
        mysqli_query($conn, $query);
        header("Location: index.php?status=sukses_edit");
        exit();
    }
}

// Mencek apakah sedang dalam mode edit (GET req_edit)
$mode_edit = false;
$data_edit = null;
if (isset($_GET['req_edit'])) {
    $mode_edit = true;
    $id = mysqli_real_escape_string($conn, $_GET['req_edit']);
    $result_edit = mysqli_query($conn, "SELECT * FROM tugas WHERE id = '$id'");
    $data_edit = mysqli_fetch_assoc($result_edit);
}

// 5. READ: Mengambil Data untuk Ditampilkan di Tabel
$data_tugas = mysqli_query($conn, "SELECT * FROM tugas ORDER BY tanggal_upload DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Sync | Academic Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f5f7ff',
                            100: '#ebf0ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            900: '#1e3a8a',
                        },
                        surface: '#ffffff',
                        background: '#fafafa',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                        'card': '0 1px 3px rgba(0,0,0,0.02), 0 1px 2px rgba(0,0,0,0.04)',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Fungsi konfirmasi hapus
        function konfirmasiHapus(url) {
            if (confirm("Apakah Anda yakin ingin menghapus data tugas beserta dokumennya? Tindakan ini tidak dapat dibatalkan.")) {
                window.location.href = url;
            }
        }
    </script>
</head>
<body class="bg-background text-gray-800 font-sans min-h-screen flex flex-col antialiased">

    <!-- Header -->
    <header class="bg-surface/80 backdrop-blur-lg border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <div class="bg-brand-50 text-brand-600 p-2.5 rounded-xl border border-brand-100">
                        <i data-lucide="layers" class="h-6 w-6"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-900 text-lg font-bold tracking-tight leading-none">Campus Sync</span>
                        <span class="text-gray-500 text-xs font-medium mt-1">Academic Resource Portal</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <?= $pesan ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- Panel Formulir (Dinamic: Tambah atau Edit) -->
            <div class="lg:col-span-4 w-full sticky top-28">
                <div class="bg-surface rounded-2xl border border-gray-100 shadow-soft p-8 relative">
                    
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-xl font-bold text-gray-900">
                            <?= $mode_edit ? 'Edit Submission' : 'New Submission' ?>
                        </h2>
                        <i data-lucide="<?= $mode_edit ? 'edit' : 'upload' ?>" class="text-gray-400 w-5 h-5"></i>
                    </div>
                    
                    <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                        
                        <?php if($mode_edit): ?>
                            <input type="hidden" name="id_edit" value="<?= $data_edit['id'] ?>">
                        <?php endif; ?>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Student ID (NIM)</label>
                            <input type="text" name="nim" value="<?= $mode_edit ? htmlspecialchars($data_edit['nim']) : '' ?>" class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl px-4 py-3 text-sm focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all outline-none placeholder-gray-400" placeholder="e.g. 210810..." required>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Full Name</label>
                            <input type="text" name="nama" value="<?= $mode_edit ? htmlspecialchars($data_edit['nama_mahasiswa']) : '' ?>" class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl px-4 py-3 text-sm focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all outline-none" required>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Course Name</label>
                            <!-- Input Course Name sekarang bebas diedit oleh pengguna -->
                            <input type="text" name="mata_kuliah" value="<?= $mode_edit ? htmlspecialchars($data_edit['mata_kuliah']) : '' ?>" class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl px-4 py-3 text-sm focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all outline-none placeholder-gray-400" placeholder="e.g. Cloud Computing" required>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Document Title</label>
                            <input type="text" name="judul_tugas" value="<?= $mode_edit ? htmlspecialchars($data_edit['judul_tugas']) : '' ?>" class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl px-4 py-3 text-sm focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all outline-none placeholder-gray-400" placeholder="e.g. Architecture Analysis" required>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                <?= $mode_edit ? 'Replace Attachment (Optional)' : 'Attachment (Required)' ?>
                            </label>
                            <?php if($mode_edit): ?>
                                <p class="text-xs text-blue-600 mb-2">Current file: <a href="uploads/<?= $data_edit['nama_file'] ?>" target="_blank" class="underline">View File</a></p>
                            <?php endif; ?>
                            <input type="file" name="<?= $mode_edit ? 'file_tugas_baru' : 'file_tugas' ?>" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100" accept=".pdf,.docx,.zip" <?= $mode_edit ? '' : 'required' ?>>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <?php if($mode_edit): ?>
                                <a href="index.php" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3.5 px-4 rounded-xl text-center transition-all text-sm">Cancel</a>
                                <button type="submit" name="submit_edit" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 px-4 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 text-sm">
                                    <i data-lucide="save" class="w-4 h-4"></i> Update
                                </button>
                            <?php else: ?>
                                <button type="submit" name="submit_tambah" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3.5 px-4 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                                    Upload Document <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel Tabel Data CRUD -->
            <div class="lg:col-span-8 w-full h-full">
                <div class="bg-surface rounded-2xl border border-gray-100 shadow-card overflow-hidden h-full flex flex-col">
                    
                    <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-surface">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Recent Submissions</h3>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto flex-grow">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100 tracking-wider font-semibold">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Student</th>
                                    <th scope="col" class="px-6 py-4">Assignment Info</th>
                                    <th scope="col" class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if(mysqli_num_rows($data_tugas) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($data_tugas)) : ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <div class="text-gray-900 font-semibold"><?= htmlspecialchars($row['nama_mahasiswa']) ?></div>
                                            <div class="text-xs text-gray-500 font-medium mt-0.5"><?= htmlspecialchars($row['nim']) ?></div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="text-gray-900 font-medium truncate max-w-[200px]"><?= htmlspecialchars($row['judul_tugas']) ?></div>
                                            <!-- Menambahkan detail Mata Kuliah pada tabel -->
                                            <div class="text-xs text-brand-600 font-medium mt-1"><?= htmlspecialchars($row['mata_kuliah']) ?></div>
                                            <div class="text-xs text-gray-500 mt-1"><?= date('d M Y', strtotime($row['tanggal_upload'])) ?></div>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="uploads/<?= $row['nama_file'] ?>" target="_blank" class="p-2 bg-green-50 text-green-600 hover:bg-green-100 rounded-lg transition-colors" title="Download">
                                                    <i data-lucide="download" class="w-4 h-4"></i>
                                                </a>
                                                <a href="index.php?req_edit=<?= $row['id'] ?>" class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Edit">
                                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                                </a>
                                                <button onclick="konfirmasiHapus('index.php?hapus=<?= $row['id'] ?>')" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="px-8 py-16 text-center text-gray-500">No submissions yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-100 bg-surface mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center">
            <p class="text-sm text-gray-500 font-medium">© 2026 Campus Sync. Designed for Academic Demo.</p>
        </div>
    </footer>

    <script>lucide.createIcons();</script>
</body>
</html>