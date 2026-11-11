# HostingRaja Support Ticket: Laravel Folder Permissions

**Subject:** Request Modify access for Laravel storage and cache folders on `dev.egovjanjgir.com`

Hello HostingRaja Support,

My Laravel application hosted at **`dev.egovjanjgir.com`** is returning HTTP 500 errors because PHP cannot write to Laravel's log and cache files. The error reports `Permission denied` for `storage\logs\laravel.log` and files under `storage\framework\cache\data`.

Plesk shows this site's application pool group as **`IWPG_abteczco`**, but I cannot grant it write/modify access because Plesk reports that **“Additional write/modify permissions management” is disabled for this subscription**.

Please grant the **`IWPG_abteczco` application pool group Modify permission**, including files and subfolders, on these two directories:

```text
D:\inetpub\vhosts\abtecz.com\dev.egovjanjgir.com\storage
D:\inetpub\vhosts\abtecz.com\dev.egovjanjgir.com\bootstrap\cache
```

Please limit the permission change to these directories. If your process requires enabling the Plesk subscription setting first, please advise or enable it and apply the folder permissions.

Please let me know once the change is complete so I can retest the website.

Thank you.
