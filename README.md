# Plan zajęć ZUT

## Instrukcja uruchomienia projektu

Po sklonowaniu repozytorium wykonaj następujące kroki (w katalogu repo):

1. `composer install`

2. `npm install`

3. `npm run dev`

4. `symfony server:start`

## W przypadku problemów

1. Nie zmieniaj nic w kodzie projektu.
2. Sprawdź, czy wszystkie wymagane zależności są zainstalowane:
    - PHP w wersji zalecanej przez Symfony.
    - Node.js w wersji 14 lub nowszej.
    - Composer.
3. Skorzystaj z pomocy ChatGPT, bo projekt działa.

---

Po zmianie css (assets/styls/app.css) wykonac `npm run dev` i Ctrl+F5 w przeglądarce

---

# Odpalenie bazy danych
1. `symfony console doctrine:database:create`
2. `symfony console doctrine:migrations:migrate`
---

# Scrapowanie danych
* Wszystko: `symfony console scrape:all`
* Nauczyciele: `symfony console scrape:teacher`
* Pokoje: `symfony console scrape:room`
* Przedmioty: `symfony console scrape:subject`
* Wydziały: `symfony console scrape:faculty`
