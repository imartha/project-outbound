<?php
require('../koneksi.php');
require('../fpdf/fpdf.php');

// buat object pdf
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();

// Judul
$pdf->SetFont('Arial','B',16);
$pdf->Cell(190,10,'Laporan Riwayat Pembayaran',0,1,'C');
$pdf->Ln(5);

// Header tabel
$pdf->SetFont('Arial','B',12);
$pdf->Cell(50,10,'Username',1,0,'C');
$pdf->Cell(40,10,'Jumlah',1,0,'C');
$pdf->Cell(50,10,'Tanggal',1,0,'C');
$pdf->Cell(50,10,'Metode',1,1,'C');

// Ambil data dari database
$query = "SELECT u.username, ph.amount_paid, ph.transfer_date, ph.payment_method
          FROM payment_history ph
          JOIN users u ON ph.user_id = u.id
          ORDER BY ph.transfer_date DESC";
$result = $koneksi->query($query);

// Isi tabel
$pdf->SetFont('Arial','',11);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(50,10,$row['username'],1,0,'C');
        $pdf->Cell(40,10,'Rp '.number_format($row['amount_paid'],0,',','.'),1,0,'C');
        $pdf->Cell(50,10,$row['transfer_date'],1,0,'C');
        $pdf->Cell(50,10,$row['payment_method'],1,1,'C');
    }
} else {
    $pdf->Cell(190,10,'Belum ada pembayaran.',1,1,'C');
}

// Cetak ke browser
$pdf->Output('I', 'Laporan_Riwayat_Pembayaran.pdf');
?>
