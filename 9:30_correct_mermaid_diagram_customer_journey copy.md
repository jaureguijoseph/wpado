flowchart TD

  subgraph Legend["Legend / Key"]
    direction LR
    UA["User Interaction"]:::userAction
    SR["Server-Side Action"]:::serverAction
    RC["RoleManager Transition"]:::roleChange
    SC["Secret Validation Check"]:::secretCheck
    ER["Error Handling"]:::errorNode
  end

  A0["Login or Create Account"]:::userAction --> A1["Assign Role: Subscriber"]:::roleChange
  A1 --> A["User Dashboard"]:::userAction
  A --> B["Click 'Sell Gift Card' Button"]:::userAction
  B --> C["Hidden Fields Captured (Name, DOB, Email, IP, etc.)"]:::userAction
  C --> D["Federal Limit Policy Check"]:::serverAction

  D -->|"Limits Exceeded"| E1["Error: Limit Exceeded"]:::errorNode
  E1 --> E2["Calculate Next Eligible Date & Amount"]:::serverAction --> E3["Display Message w/ Date & Amount"]:::userAction --> R1["RoleManager: Revert to Subscriber"]:::roleChange --> R2["Redirect to Dashboard"]:::userAction

  D -->|"Within Limits"| R0["RoleManager: Subscriber → Plaid User"]:::roleChange --> F1["WS Form: Gift Card Type Selection"]:::userAction
  F1 -->|"Visa/Mastercard/Amex/Discover"| F2["Begin Plaid OAuth → Bank Account Linking"]:::userAction
  F1 -->|"Other Gift Cards"| F3["Error: Unsupported Gift Card Type"]:::errorNode --> R2

  F2 --> SC1["Secret Validation Check #1"]:::secretCheck
  SC1 -->|"Failed"| ER1["Error: Secret Validation Failed"]:::errorNode --> R1
  SC1 -->|"Passed"| G["Plaid Modal: Bank Selection & Authentication"]:::userAction

  G --> G1{{"Plaid Modal Result"}}:::serverAction
  G1 -->|"Exit"| ER2["User Cancelled - Reset Status"]:::errorNode --> R1
  G1 -->|"Error/Cancel"| ER3["Handle Plaid Error"]:::errorNode --> ER4["Error Type"]:::serverAction
  ER4 -->|"Temporary"| RETRY1["Show Retry Option"]:::userAction --> F2
  ER4 -->|"Bank Not Supported"| ER5["Bank Compatibility Error"]:::errorNode --> R1
  ER4 -->|"Auth Failed"| ER6["Authentication Error + Retry"]:::errorNode --> F2

  G1 -->|"Success"| H["RTP/FedNow Capability Check"]:::serverAction --> H1{{"Bank Compatible?"}}:::serverAction
  H1 -->|"No"| ER7["Bank Incompatibility Error"]:::errorNode --> R1
  H1 -->|"Yes"| I["Plaid Identity Verifier"]:::serverAction

  I --> SC2["Secret Validation Check #2"]:::secretCheck
  SC2 -->|"Failed"| ER8["Error: Secret Validation Failed"]:::errorNode --> R1
  SC2 -->|"Passed"| J["RoleManager: Plaid User → Transaction User"]:::roleChange

  J --> K["WS Form: Enter Gift Card Amount"]:::userAction --> K1["Server-Side Payout Offer Calculation"]:::serverAction --> K2["Click 'I Accept Offer & Agree to Terms'"]:::userAction
  K2 --> SC3["Secret Validation Check #3"]:::secretCheck
  SC3 -->|"Failed"| ER9["Error: Secret Validation Failed"]:::errorNode --> R1
  SC3 -->|"Passed"| L["Authorize.Net Accept.js Modal Appears"]:::userAction

  L --> L1["User Inputs Card Info + Clicks 'Pay Now'"]:::userAction --> M["Authorize & Capture Transaction"]:::serverAction
  M -->|"Success"| N["RoleManager: Transaction User → PAYMENT"]:::roleChange --> SC4["Secret Validation Check #4"]:::secretCheck
  SC4 -->|"Failed"| ER10["Error: Secret Validation Failed"]:::errorNode --> R1
  SC4 -->|"Passed"| O["PayoutManager: Calculate Final Net Payout"]:::serverAction
  O --> P["Initiate RTP/FedNow Payout via Plaid"]:::serverAction --> SC5["Secret Validation Check #5"]:::secretCheck
  SC5 -->|"Failed"| ER11["Error: Secret Validation Failed"]:::errorNode --> R1
  SC5 -->|"Passed"| Q["Transaction Complete Notification"]:::serverAction --> R["RoleManager: PAYMENT → Subscriber"]:::roleChange --> S["Display Success Dashboard"]:::userAction

  M -->|"Declined"| M1["Handle Payment Declined"]:::errorNode --> M2{{"Retry Allowed?"}}:::serverAction
  M2 -->|"Yes"| RETRY2["Show Retry Option"]:::userAction --> L
  M2 -->|"No"| ER12["Final Payment Error"]:::errorNode --> R1

  M -->|"Error"| M3["Handle Payment Error"]:::errorNode --> L
  M -->|"Cancel"| M4["User Cancelled Payment"]:::errorNode --> R1

  classDef userAction fill:#eef,stroke:#4472c4,stroke-width:2px
  classDef roleChange fill:#d9f2ff,stroke:#00aaff,stroke-width:2px
  classDef serverAction fill:#fff2cc,stroke:#e69900,stroke-width:2px
  classDef secretCheck fill:#e6e6fa,stroke:#8a2be2,stroke-width:2px
  classDef errorNode fill:#ffd6d6,stroke:#cc0000,stroke-width:2px