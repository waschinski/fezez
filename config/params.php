<?php

return [
    'homeURL' => getenv('HOME_URL'),
    'adminEmail' => getenv('MAIL_ADMIN'),
    'senderEmail' => getenv('MAIL_SENDER'),
    'senderName' => getenv('MAIL_SENDERNAME'),
    'user.passwordResetTokenExpire' => 3600,
    // !!! insert a secret key in the following (if it is empty) - this is required for key encryption
    'secretKey' => getenv('SECRETKEY'),
    'InvitationMandatory' => getenv('INVITATION_MANDATORY'),
    'discordWebhookURL' => getenv('DISCORD_WEBHOOK_URL'),
];
