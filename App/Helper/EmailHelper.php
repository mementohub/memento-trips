<?php

namespace App\Helper;

use Modules\EmailSetting\App\Models\EmailSetting;

/**
 * EmailHelper
 *
 * Configures Laravel's SMTP mailer at runtime using credentials
 * stored in the `email_settings` database table. Called before
 * sending any transactional email to ensure the mailer uses the
 * admin-configured SMTP host, port, encryption, and sender details.
 *
 * @package App\Helper
 */
class EmailHelper
{
    /**
     * Load SMTP settings from the database and apply them to the mailer config.
     *
     * Reads keys: mail_host, mail_port, mail_encryption, smtp_username,
     * smtp_password, email (from address), sender_name (from name).
     *
     * @return void
     */
    public static function mail_setup(): void
    {
        $setting_data = EmailSetting::all();

        $email_setting = [];
        foreach ($setting_data as $data_item) {
            $email_setting[$data_item->key] = $data_item->value;
        }
        $email_setting = (object)$email_setting;

        // Apply SMTP transport configuration
        config(['mail.mailers.smtp' => [
                'transport' => 'smtp',
                'host' => $email_setting->mail_host,
                'port' => $email_setting->mail_port,
                'encryption' => $email_setting->mail_encryption,
                'username' => $email_setting->smtp_username,
                'password' => $email_setting->smtp_password,
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
            ]]);

        config(['mail.from.address' => $email_setting->email]);
        config(['mail.from.name' => $email_setting->sender_name]);
    }
}