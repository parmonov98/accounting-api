# Accounting API Project

## SOLID Principles Implementation

### Micro-level (Class Level)

1. **Single Responsibility Principle (SRP)**
   - `TransactionService` handles only transaction-related business logic
   - `ExchangeRateFactory` is solely responsible for creating exchange rate drivers
   - Each driver (`XmlDriver`, `JsonDriver`, `CsvDriver`) handles only its specific format

2. **Open/Closed Principle (OCP)**
   - Exchange rate system is extensible through drivers without modifying existing code
   - New drivers can be added by implementing `ExchangeRateDriverInterface`
   - Currency conversion strategies can be added without changing core logic

3. **Liskov Substitution Principle (LSP)**
   - All exchange rate drivers implement `ExchangeRateDriverInterface`
   - Any driver can be used interchangeably in the `AverageDriver`
   - Base `Controller` class is properly extended by `TransactionController`

4. **Interface Segregation Principle (ISP)**
   - `ExchangeRateDriverInterface` is focused and minimal
   - DTOs are specific to their use cases (e.g., `TransactionDTO`)
   - Resource classes are tailored to their specific response formats

5. **Dependency Inversion Principle (DIP)**
   - Controllers depend on interfaces rather than concrete implementations
   - Services are injected through constructor dependency injection
   - Currency facade provides abstraction over concrete implementations

### Macro-level (System Architecture)

1. **Modular Architecture**
   - Separate modules for Transactions and Currency
   - Each module is self-contained with its own routes, controllers, and services
   - Clear separation between API endpoints and business logic

2. **Service Layer Pattern**
   - Business logic encapsulated in service classes
   - Controllers are thin and focused on request/response handling
   - Clear separation of concerns between layers

3. **Factory Pattern**
   - `ExchangeRateFactory` creates appropriate drivers
   - Abstraction of driver creation logic
   - Easy to add new drivers without changing client code

4. **Repository Pattern**
   - Data access logic is separated from business logic
   - Models handle database interactions
   - Easy to switch data sources if needed

## PHPDoc Usage and Best Practices

Good PHPDoc usage means providing clear, useful documentation that helps other developers understand and use your code. Examples from our codebase:

```php
/**
 * Converts an amount from one currency to another using the current exchange rate.
 *
 * @param float $amount The amount to convert
 * @param string $from The source currency code (e.g., 'EUR')
 * @param string $to The target currency code (e.g., 'USD')
 * @return float The converted amount
 * @throws InvalidArgumentException If currency codes are invalid
 */
public function getRate(string $from, string $to): float
```

Key aspects of proper PHPDoc:
1. Clear description of the method's purpose
2. All parameters documented with types and descriptions
3. Return type and possible exceptions documented
4. Real-world examples in descriptions when helpful
5. Type hints that help IDE autocompletion

## PHP 7+ Features I Like/Dislike

### Likes:
1. **Type Declarations**
   - Return type declarations
   - Property type declarations
   - Strict typing support
   ```php
   public function getRate(string $from, string $to): float
   ```

2. **Null Coalescing Operator**
   ```php
   $driver = $driver ?? config('currency.default_driver', 'average');
   ```

3. **Arrow Functions**
   - Cleaner syntax for simple callbacks
   ```php
   $rates = array_map(fn($rate) => $rate * 1.1, $baseRates);
   ```

4. **Constructor Property Promotion**
   ```php
   public function __construct(
       private TransactionService $transactionService
   ) {}
   ```

5. **Match Expression**
   ```php
   return match($driver) {
       'xml' => new XmlDriver(),
       'json' => new JsonDriver(),
       default => throw new \InvalidArgumentException("Driver not supported"),
   };
   ```

### Don't like:
1. **Generics Support**
   - Would be helpful for collections and repositories
   - Currently requires PHPDoc annotations

2. **Property Accessor Syntax**
   - No built-in property accessor syntax

3. **Async/Await**
   - Limited support for asynchronous programming
   - Requires external libraries or complex implementations

The codebase demonstrates modern PHP practices while working around these limitations through careful design and architecture.

### My View on PHP 7+ Type System

**When to Use Types:**
1. Value Objects and DTOs for ensuring data integrity
2. Service layer methods where business logic correctness is crucial

**When to Skip Types:**
1. Data transformation layers where flexibility is needed
