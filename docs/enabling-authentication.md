# Enabling authentication

## Step 1: Migrate the users table

```sql
insert into kuma_rest_users (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, admin_locale, password_changed, google_id, api_key)
select *, null from kuma_users
```

## Step 2: Add config to security.yml

```yaml
    firewalls:
        api_public:
            pattern: ^/api/public
            security: false
        api:
            pattern: ^/api/
            guard:
                authenticators:
                    - kunstmaan_rest_core.api_authenticator
            security: true
            anonymous: ~
            logout: ~
```
