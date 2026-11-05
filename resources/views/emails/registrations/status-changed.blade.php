<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Status Pendaftaran</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f5f7f6; font-family: Arial, Helvetica, sans-serif; color: #1f2933;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f7f6; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width: 560px; background-color: #ffffff; border-radius: 12px; overflow: hidden;">
                    <tr>
                        <td style="background-color: #0f4c43; padding: 24px 32px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 20px;">Bandung Care</h1>
                            <p style="margin: 4px 0 0; color: #cfe6e1; font-size: 14px;">Update Status Pendaftaran Volunteer</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 16px; font-size: 15px;">Halo <strong>{{ $registration->full_name }}</strong>,</p>
                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6;">
                                Status pendaftaran Anda pada event <strong>{{ $registration->event?->title }}</strong> telah diperbarui.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border: 1px solid #dde5e2; border-radius: 8px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #dde5e2; font-size: 14px; color: #52606d;">
                                        Kode Pendaftaran
                                    </td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #dde5e2; font-size: 14px; font-weight: bold;">
                                        {{ $registration->registration_code }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #dde5e2; font-size: 14px; color: #52606d;">
                                        Event
                                    </td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #dde5e2; font-size: 14px;">
                                        {{ $registration->event?->title }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 14px; color: #52606d;">Status</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: bold; color: #0f4c43;">
                                        {{ $statusLabel }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6;">{{ $statusMessage }}</p>

                            @if ($notes)
                                <p style="margin: 0 0 24px; font-size: 14px; line-height: 1.6; padding: 12px 16px; background-color: #f5f7f6; border-radius: 8px;">
                                    <strong>Catatan panitia:</strong><br>
                                    {{ $notes }}
                                </p>
                            @endif

                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6;">
                                Anda dapat memeriksa status pendaftaran kapan saja melalui tautan berikut.
                            </p>

                            <p style="margin: 0 0 8px;">
                                <a href="{{ $trackingUrl }}"
                                    style="display: inline-block; background-color: #b91c4b; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: bold;">
                                    Cek Status Pendaftaran
                                </a>
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #7b8794; word-break: break-all;">{{ $trackingUrl }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 32px; background-color: #f5f7f6; font-size: 12px; color: #7b8794;">
                            Email ini dikirim otomatis oleh sistem Bandung Care. Mohon tidak membalas email ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
