<?php
/**
 * Email al cliente cuando se crea un reporte de su jardín.
 * Variables: $report (con address, user_name, visit_date, grass_health, etc.), $viewReportUrl, $logoUrl
 */
$report = $report ?? [];
// El controller garantiza URL absoluta; evitar # para que no abra about:blank
$viewReportUrl = (!empty($viewReportUrl) && $viewReportUrl !== '#') ? $viewReportUrl : 'https://www.cesped365.com/?go=login';
$clientName = $report['user_name'] ?? 'Cliente';
$address = $report['address'] ?? '';
$visitDate = $report['visit_date'] ?? '';
$grassHealth = $report['grass_health'] ?? 'bueno';
$grassLabels = ['excelente' => 'Excelente', 'bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo'];
$grassLabel = $grassLabels[$grassHealth] ?? ucfirst($grassHealth);
$technicianNotes = $report['technician_notes'] ?? '';
$workDone = $report['work_done'] ?? '';
$recommendations = $report['recommendations'] ?? '';
$wateringStatus = $report['watering_status'] ?? 'optimo';
$wateringLabels = ['optimo' => 'Óptimo', 'insuficiente' => 'Insuficiente', 'excesivo' => 'Excesivo'];
$wateringLabel = $wateringLabels[$wateringStatus] ?? $wateringStatus;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nuevo reporte - Cesped365</title>
</head>
<body style="margin:0; padding:0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f3f4f6;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6; padding: 24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
          <!-- Logo -->
          <tr>
            <td style="padding: 28px 32px 16px; text-align: center; border-bottom: 1px solid #e5e7eb;">

              <span style="font-size: 26px; font-weight: 700; color: #166534;">Cesped365</span>

            </td>
          </tr>
          <!-- Título -->
          <tr>
            <td style="padding: 24px 32px 8px;">
              <h1 style="margin: 0; font-size: 20px; font-weight: 600; color: #111827;">Hola, <?= esc($clientName) ?></h1>
            </td>
          </tr>
          <tr>
            <td style="padding: 0 32px 20px;">
              <p style="margin: 0; font-size: 15px; color: #4b5563; line-height: 1.5;">Tenés un nuevo reporte de mantenimiento de tu jardín.</p>
            </td>
          </tr>
          <!-- Detalle del reporte -->
          <tr>
            <td style="padding: 0 32px 24px;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                <tr>
                  <td style="padding: 20px 20px 16px;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                      <?php if ($visitDate): ?>
                      <tr>
                        <td style="padding: 4px 0; font-size: 13px; color: #6b7280;">Fecha de visita</td>
                        <td style="padding: 4px 0; font-size: 13px; color: #111827; font-weight: 500;" align="right"><?= esc($visitDate) ?></td>
                      </tr>
                      <?php endif; ?>
                      <tr>
                        <td style="padding: 4px 0; font-size: 13px; color: #6b7280;">Jardinero</td>
                        <td style="padding: 4px 0; font-size: 13px; color: #111827; font-weight: 500;" align="right"><?= esc($technicianNotes !== '' ? $technicianNotes : '—') ?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- Botón / Link (siempre visible; href debe ser URL completa en producción) -->
          <tr>
            <td style="padding: 0 32px 32px; text-align: center;">
              <table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin: 0 auto;">
                <tr>
                  <td align="center" style="background-color: #166534; border-radius: 8px;">
                    <a href="<?= esc($viewReportUrl) ?>" rel="noopener noreferrer" style="display: inline-block; padding: 14px 28px; color: #ffffff !important; text-decoration: none; font-size: 14px; font-weight: 600;">Iniciar sesión para ver tu reporte</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding: 0 32px 24px; text-align: center;">
              <p style="margin: 0; font-size: 15px; color: #212121;">Iniciá sesión en tu cuenta para ver el reporte completo, <br> fotos y dejar valoración del servicio. <br> ¡Gracias!</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>