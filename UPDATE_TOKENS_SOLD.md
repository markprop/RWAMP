# How to Update Tokens Sold to 8M

## Quick Method

Add this to your `.env` file:

```env
PRESALE_TOKENS_SOLD_OVERRIDE=8000000
```

Then clear config cache:
```bash
php artisan config:clear
```

## What This Does

- Sets the "Tokens Sold" display to **8,000,000 RWAMP** (8M)
- Automatically updates the progress bar to show: **8,000,000 RWAMP of 60,000,000 RWAMP** (13.33% Complete)
- Updates the "Total Raised" amount based on current token price × 8M

## Configuration

The override is set in `config/crypto.php`:
```php
'tokens_sold_override' => (float) env('PRESALE_TOKENS_SOLD_OVERRIDE', 0),
```

- **0** = Use calculated value from transactions (default)
- **Any positive number** = Override with this value

## Example

To set 8M tokens sold:
```env
PRESALE_TOKENS_SOLD_OVERRIDE=8000000
```

To set 10M tokens sold:
```env
PRESALE_TOKENS_SOLD_OVERRIDE=10000000
```

To use calculated value (remove override):
```env
PRESALE_TOKENS_SOLD_OVERRIDE=0
```

## After Changing

1. Update `.env` file
2. Run: `php artisan config:clear`
3. Refresh the page

The progress bar and all related calculations will update automatically.
