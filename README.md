

# Circle Pay




## Quick start

Setup `php` and `composer` first. 
https://gist.github.com/vkabc/b0805966d8ef86677767670e5dd256dd


Add `VITE_API_KEY=` to  `.env` with the bearer key from circle sandbox api. 

```bash
#alternatively, you can setup postgres, easy quick start using sqlite
touch database/database.sqlite

#change the DB_* settings accordingly to which database you use, easiest is sqlite. DB_CONNECTION=sqlite and the rest DB_* empty value.
mv .env.example .env 

php artisan migrate

artisan serve
```

## Circle API used
1) Payments API, to allow credit card payment.
2) Accounts API, to allow receiving on chain usdc transfers.
3) Accounts API, to distribute funds to shop tenants after they create their circle accounts. 


