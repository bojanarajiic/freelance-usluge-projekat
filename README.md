# OPIS APLIKACIJE
Ovaj projekat je kreiran za potrebe predmeta _Serverske veb tehnologije_.
Projekat predstavlja backend API za platformu koja povezuje klijente (client) i frilensere (freelancer). Klijenti objavljuju projekte, frilenseri šalju ponude, prihvatanjem ponude nastaje ugovor, a po završetku obe strane ostavljaju recenzije. Postoji i admin uloga za održavanje šifarnika veština. Autentikacija preko Laravel Sanctum.

## Pregled funkcionalnosti
 - Nalozi i profili: registracija, prijava, odjava, profil (bio, linkovi, lokacija, satnica), promena/reset lozinke.

 - Veštine (skills): javni pregled, dodela korisniku i označavanje projekata traženim veštinama.

 - Projekti: objava, izmene, statusi (open/in_progress/completed/cancelled), brisanje sa povezanim podacima.

 - Ponude: slanje, izmena/povlačenje, prihvatanje/odbijanje na nivou projekta.

 - Ugovori: nastaju iz prihvaćene ponude, prate status (active/completed/cancelled).

 - Recenzije: obostrano ocenjivanje po projektu; pregled po korisniku i po projektu.

## Pokretanje aplikacije
      cd projekatlaravel
      php artisan serve
      php artisan migrate:fresh --seed

