# Fuentes para PDFs TCPDF

Colocar aquí los archivos TrueType de **Arial** para que los PDF generados con TCPDF usen esa tipografía (UTF-8):

- `arial.ttf` (regular) — obligatorio para Arial real
- `arialbd.ttf` (negrita) — opcional

En Windows el sistema también puede tomarlos de `C:\Windows\Fonts\` si no están en esta carpeta.

Sin `arial.ttf`, TCPDF usará `helvetica` (sustituto limitado para tildes y ñ).

Uso en código: `App\Support\Pdf\TcpdfFuenteArial::aplicar($pdf, 'B', 9);`
