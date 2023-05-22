

## Circle Pay


![image](https://github.com/vkabc/circle-pay/assets/4152386/4ddbc38c-2ee5-4f9a-bfb6-80532bcd4d3b)




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

## 
