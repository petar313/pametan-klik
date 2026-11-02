# Pametan klik

Novi pametanklik.rs: saveti o bezbednosti dece na internetu, za decu i roditelje, na srpskom (ćirilica i latinica), engleskom i španskom.

Symfony 8.1 na PHP 8.5 (FrankenPHP), Twig na serveru, Vue 3 preko Vite samo za interaktivne delove, sadržaj u Markdown fajlovima, bez baze.

## Pokretanje

```sh
composer install
echo "APP_SECRET=$(openssl rand -hex 16)" >> .env.local   # jednom, tajna se ne commituje
npm install && npm run build      # ili `npm run dev` dok radiš na assets/
symfony serve                     # ili: docker compose up --build  ->  http://localhost:8080
vendor/bin/phpunit                # testovi
```

## Kako je organizovano

| Šta | Gde |
|---|---|
| Tekstovi stranica | `content/<jezik>/<sekcija>/<slug>.md` (`sr`, `en`, `es`); `index.md` je početna sekcije |
| Prevodi menija i šablona | `translations/messages.<locale>.yaml` |
| Jezici, sekcije, hreflang | `src/Site/Site.php` |
| Stari URL-ovi -> novi (301) | `config/legacy_redirects.yaml` |
| Vue komponente | `assets/vue/*.vue`, montiraju se sa `<div data-vue="Ime">` |
| Slike (karakondžulice, mala Maja, logoi) | `public/images/` |
| Kontakt forma | `src/Controller/ContactController.php`, `src/Form/ContactType.php` |

Svaki Markdown fajl ima frontmatter `key`, `title` i `description`. Isti `key` povezuje prevode jedne stranice (za `hreflang` i izbor jezika).

**Ćirilica i latinica:** srpski se piše samo na ćirilici. Stranice pod `/lat/...` se renderuju iz istog teksta, a `LatinScriptListener` preslovljava gotov HTML u latinicu.

**Adrese:** `/` i `/deca/cet` (ćirilica), `/lat/deca/cet` (latinica), `/en/kids/chat`, `/es/ninos/chat`. Sekcije: `deca`/`roditelji`, `kids`/`parents`, `ninos`/`padres`.

## Promenljive okruženja

| Promenljiva | Značenje |
|---|---|
| `APP_SECRET` | Symfony tajna |
| `ADSENSE_CLIENT` | Google AdSense `ca-pub-...`; prazno = bez oglasa |
| `MAILER_DSN` | SMTP za kontakt formu, npr. `smtp://user:pass@smtp.example.com:587` |
| `CONTACT_FROM` | Pošiljalac poruka sa forme (adresa na domenu sajta) |
| `CONTACT_TO` | Gde stižu poruke sa forme (`office@items.rs`) |
