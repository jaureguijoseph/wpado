# Corrected Customer Journey (Matches Updated PRD)

```mermaid
---
config:
  layout: dagre
---
flowchart TD
 subgraph Legend["Legend / Key"]
    direction LR
        UA["User Interaction"]
        SR["Server-Side Action"]
        RC["RoleManager Transition"]
        SC["Secret Validation Check"]
        ER["Error Handling"]
  end
    A0["Login or Create Account"] --> A1["Assign Role: Subscriber"]
    A1 --> A["User Dashboard"]
    A --> B["Hidden Form Button Click"]
    B --> C["Form Collects User Data"]
    C --> D["Generate Invoice Number + Capture Current IP Address"]
    D --> E["Server-Side Federal Limit Policy Check"]
    E -- Limits NOT Exceeded --> F["RoleManager: Subscriber → Plaid User"]
    F --> G["Begin Plaid OAuth → Bank Account Linking With PLAID Modal"]
    G --> G2["Secret Validation Check #1"]
    G2 --> H["RTP Compatibility Checker: RTP User Bank Account Supported?"]
    H --> I["Plaid Identity Verifier"] & F1["Error: Bank Not Compatible"]
    I --> J["Secret Validation Check #2"] & F2["Error: Identity Verification Failed"]
    J --> K["RoleManager: Plaid User → Transaction User"] & F3["Error: Secret Validation Failed"]
    K --> L0["User Confirms Gift Card Type (Visa, MasterCard, Amex, Discover ONLY)"]
    L0 --> L1["WS Form Authorize.net Extension: Enter Gift Card Amount"]
    L1 --> L2["Server-Side Calculation: Display Payout Offer"]
    L2 --> L3["WS Form Pro Form User Agrees to Terms + Clicks "I Accept Offer""]
    L3 --> N["Secret Validation Check #3 (PRE-PAYMENT SECURITY)"]
    N --> L4["Authorize.Net Accept.js Modal Appears"] & F4["Error: Secret Validation Failed"]
    L4 --> L5["User Inputs Card Info + Clicks "Pay Now""]
    %% --- NEW: split success vs failure on the Authorize.net attempt ---
    L5 --> M["Authorize.net Attempts Authorize & Capture Transaction Manager"]
    M -- Success --> O["RoleManager: Transaction User → PAYMENT"]
    M -- Failure (bad amount, card data error, etc.) --> M1["Authorize.Net Modal: Show Errors & Allow User to Edit/Retry"]
    M1 -- Retry --> L5
    M1 -- After Maximum Fails: Cancel Transaction --> M2["Authorize.Net Cancels Transaction"]
    M2 --> R1["RoleManager: Revert to Subscriber"]
    %% --- CONTINUE SUCCESS PATH ---
    O --> P["PayoutManager: Server-Side Fee Calculation & Final Amount"]
    P --> Q["Secret Validation Check #4 (FINAL VERIFICATION)"]
    Q --> R["Plaid RTP: Instant Deposit to Linked Account"] & F5["Error: Secret Validation Failed"]
    R --> S["Notifications"]
    S --> T["User Dashboard: Transaction Complete"]
    E -- Limits Exceeded --> XE["Error: Limit Exceeded"]
    XE --> XF["Server-Side: Calculate Next Eligible Date & Amount"]
    XF --> XG["Display Polite Message w Next Date And Dollar Amount User Is Eligible For"]
    XG --> XT["User is Automatically Redirected Back To User Dashboard"]
    F1 --> R1
    F2 --> R1
    F3 --> R1
    F4 --> R1
    F5 --> R1
    R1 --> R2["Redirect to User Dashboard"]
     UA:::userAction
     SR:::serverAction
     RC:::roleChange
     SC:::secretCheck
     ER:::errorNode
     A0:::userAction
     A1:::roleChange
     A:::userAction
     B:::userAction
     C:::userAction
     D:::serverAction
     E:::serverAction
     F:::roleChange
     G:::userAction
     G2:::secretCheck
     H:::serverAction
     I:::serverAction
     F1:::errorNode
     J:::secretCheck
     F2:::errorNode
     K:::roleChange
     F3:::errorNode
     L0:::userAction
     L1:::userAction
     L2:::serverAction
     L3:::userAction
     N:::secretCheck
     L4:::userAction
     L5:::userAction
     M:::serverAction
     M1:::userAction
     M2:::errorNode
     O:::roleChange
     F4:::errorNode
     P:::serverAction
     Q:::secretCheck
     R:::serverAction
     F5:::errorNode
     S:::serverAction
     T:::userAction
     XE:::errorNode
     XF:::serverAction
     XG:::userAction
     XT:::userAction
     R1:::roleChange
     R2:::userAction
    classDef userAction fill:#eef,stroke:#4472c4,stroke-width:2px
    classDef roleChange fill:#d9f2ff,stroke:#00aaff,stroke-width:2px
    classDef serverAction fill:#fff2cc,stroke:#e69900,stroke-width:2px
    classDef secretCheck fill:#e6e6fa,stroke:#8a2be2,stroke-width:2px
    classDef errorNode fill:#ffd6d6,stroke:#cc0000,stroke-width:2px
    ```

## Key Changes Made to Match Updated PRD:

### 1. Secret Validation Check #3 Timing (CRITICAL FIX)
- **Moved** Secret Validation Check #3 to happen BEFORE Authorize.net transaction
- **Added** "(PRE-PAYMENT SECURITY)" label for clarity
- **Flow:** Now happens at step N, before L4 (Authorize.Net modal)

### 2. Role Name Standardization (MINOR FIX)
- **Changed** "Payout User" to "PAYMENT" in step O
- **Matches** the standardized role naming from the corrected PRD

### 3. Additional Clarifications
- **Added** "(FINAL VERIFICATION)" label to Secret Validation Check #4
- **Fixed** typo: "Eligiable" to "Eligible"
- **Clarified** RTP Compatibility Checker step naming

### 4. Federal Limit Values (Reference)
The diagram references federal limits that are now standardized in the PRD as:
- $500 in last 24 hours
- $1,500 in last 7 days  
- $2,500 month-to-date
- $8,500 year-to-date

This corrected Mermaid diagram now fully aligns with the updated PRD document and implements all the conflict resolutions.

