<?php /* #?ini charset="utf-8"?

[CronjobSettings]
ScriptDirectories[]=extension/sso_jwt/cronjobs
Scripts[]=remove_session_ips.php
Scripts[]=remove_expired_tokens.php

[CronjobPart-frequent]
Scripts[]=remove_session_ips.php
Scripts[]=remove_expired_tokens.php

*/ ?>
