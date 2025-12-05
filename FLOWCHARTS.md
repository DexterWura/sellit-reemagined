# Marketplace System Flowcharts

## 1. Account Creation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER ACCOUNT CREATION                        │
└─────────────────────────────────────────────────────────────────┘

START
  │
  ├─► User visits /register
  │
  ├─► System displays registration form
  │
  ├─► User fills in:
  │     • Full Name (min 3 chars, must contain letters)
  │     • Email (unique, validated)
  │     • Mobile Number (unique, numeric only)
  │     • Country & Country Code
  │     • Password (min 6 chars, secure if enabled)
  │     • Agree to Terms (if required)
  │     • Captcha verification
  │
  ├─► System validates:
  │     • Full name format (not just numbers)
  │     • Email uniqueness
  │     • Mobile uniqueness
  │     • Password strength
  │     • Captcha validity
  │
  ├─► [Validation Failed?]
  │     │
  │     └─► Display error messages
  │         └─► Return to form
  │
  ├─► [Validation Passed?]
  │     │
  │     ├─► Generate unique username from email
  │     │     • Extract email prefix
  │     │     • Remove special characters
  │     │     • Check uniqueness, append number if needed
  │     │
  │     ├─► Split fullname into firstname/lastname
  │     │
  │     ├─► Create User record:
  │     │     • Set email, username, password (hashed)
  │     │     • Set mobile, country info
  │     │     • Set verification flags (ev, sv, kv based on settings)
  │     │     • Set profile_complete = YES
  │     │     • Set ref_by if referral exists
  │     │
  │     ├─► Create Admin Notification
  │     │
  │     ├─► Create UserLogin record (IP, location, browser)
  │     │
  │     ├─► Link pending escrows (if email matches invitation)
  │     │
  │     ├─► Log registration success
  │     │     • User ID, username, email
  │     │     • IP address, user agent
  │     │     • Referrer info
  │     │
  │     ├─► Fire Registered event
  │     │
  │     ├─► Auto-login user
  │     │
  │     └─► Redirect to dashboard
  │
END
```

## 2. Login Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER LOGIN                                │
└─────────────────────────────────────────────────────────────────┘

START
  │
  ├─► User visits /login
  │
  ├─► System displays login form
  │
  ├─► User enters:
  │     • Username/Email
  │     • Password
  │     • Captcha (if enabled)
  │
  ├─► System validates:
  │     • Username/email format
  │     • Password required
  │     • Captcha validity
  │
  ├─► [Validation Failed?]
  │     │
  │     └─► Display error messages
  │         └─► Return to form
  │
  ├─► [Too Many Login Attempts?]
  │     │
  │     └─► Lock account temporarily
  │         └─► Display lockout message
  │
  ├─► System attempts authentication
  │     • Check username/email exists
  │     • Verify password hash
  │
  ├─► [Login Failed?]
  │     │
  │     ├─► Log failed attempt
  │     │     • Username attempted
  │     │     • IP address
  │     │     • User agent
  │     │
  │     ├─► Increment login attempts
  │     │
  │     └─► Display error message
  │         └─► Return to form
  │
  ├─► [Login Successful?]
  │     │
  │     ├─► Log successful login
  │     │     • User ID, username, email
  │     │     • IP address, user agent
  │     │     • Login method (email/username)
  │     │
  │     ├─► Create UserLogin record:
  │     │     • IP address & location
  │     │     • Browser & OS info
  │     │     • Timestamp
  │     │
  │     ├─► Toggle 2FA verification flag
  │     │
  │     ├─► Clear login attempts counter
  │     │
  │     ├─► Check intended route (if exists)
  │     │
  │     └─► Redirect to:
  │         • Intended route OR
  │         • Dashboard
  │
END
```

## 3. Creating a Listing Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    CREATING A LISTING                           │
└─────────────────────────────────────────────────────────────────┘

START
  │
  ├─► User clicks "Create Listing"
  │
  ├─► System checks:
  │     • User is authenticated
  │     • User account is active
  │     • Rate limit check (max 10 listings per 24h)
  │
  ├─► [Rate Limit Exceeded?]
  │     │
  │     └─► Display error: "Max listings per day reached"
  │
  ├─► System displays listing creation form
  │     • Business types (domain, website, social media, etc.)
  │     • Sale types (fixed price, auction)
  │     • Categories
  │
  ├─► User selects:
  │     • Business Type
  │     • Sale Type (Fixed Price OR Auction)
  │     • Domain/Website URL (if applicable)
  │     • Title, Tagline, Description
  │     • Category
  │     • Pricing:
  │       - Fixed Price: Asking Price
  │       - Auction: Starting Bid, Reserve Price, Buy Now Price, Duration
  │     • Financials (revenue, profit, traffic)
  │     • Images
  │     • Confidential/NDA settings
  │
  ├─► System validates:
  │     • Business type allowed
  │     • Sale type allowed
  │     • Domain/URL format & uniqueness
  │     • Description length (min/max)
  │     • Price ranges
  │     • Auction duration limits
  │     • Image formats & sizes
  │
  ├─► [Validation Failed?]
  │     │
  │     └─► Display errors
  │         └─► Return to form with input
  │
  ├─► [Business Logic Validation]
  │     │
  │     ├─► Auction: Reserve > Starting Bid
  │     ├─► Auction: Buy Now > Starting Bid
  │     ├─► Profit <= Revenue
  │     └─► Domain not already listed
  │
  ├─► [Business Logic Failed?]
  │     │
  │     └─► Display validation errors
  │
  ├─► System creates Listing:
  │     • Generate listing_number (unique)
  │     • Generate slug (title-based + random)
  │     • Set status = PENDING
  │     • Set user_id
  │     • Store all listing data
  │
  ├─► System processes images:
  │     • Upload to storage
  │     • Create ListingImage records
  │     • Set first image as primary
  │
  ├─► System logs listing creation:
  │     • Listing ID, number
  │     • User info
  │     • Business type, sale type
  │     • Pricing info
  │     • IP address
  │
  ├─► Increment user's total_listings count
  │
  ├─► Clear draft data (if exists)
  │
  ├─► Display success message
  │
  └─► Redirect to "My Listings"
END
```

## 4. Auction Bidding Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      AUCTION BIDDING                            │
└─────────────────────────────────────────────────────────────────┘

START
  │
  ├─► User views active auction listing
  │
  ├─► User clicks "Place Bid"
  │
  ├─► System checks:
  │     • Listing is active auction
  │     • Auction hasn't ended
  │     • User is not the seller
  │     • User email & mobile verified
  │     • Not in last 30 seconds (anti-sniping)
  │
  ├─► [Checks Failed?]
  │     │
  │     └─► Display error message
  │
  ├─► User enters bid amount (and optional max bid for auto-bidding)
  │
  ├─► System validates:
  │     • Bid >= minimum_bid (current_bid + increment)
  │     • Bid <= reasonable maximum (10x current or 100x starting)
  │     • No existing active bid from same user
  │     • Rate limit check (max 10 bids per 5 min)
  │
  ├─► [Validation Failed?]
  │     │
  │     └─► Display error with specific reason
  │
  ├─► BEGIN TRANSACTION (Lock listing row)
  │
  ├─► System processes bid:
  │     • Mark previous winning bid as OUTBID
  │     • Notify outbid user
  │     • Create new Bid record:
  │       - Status = WINNING
  │       - Amount, max_bid, is_auto_bid
  │       - IP address logged
  │     • Update listing:
  │       - current_bid = new bid amount
  │       - highest_bidder_id = user_id
  │       - Increment total_bids
  │
  ├─► Check auto-extend (if bid in last X minutes, extend auction)
  │
  ├─► COMMIT TRANSACTION
  │
  ├─► Log bid placement:
  │     • Bid ID, listing ID
  │     • User info, amount
  │     • Previous/current highest
  │     • Time remaining
  │
  ├─► Notify seller of new bid
  │
  ├─► Notify watchlist users (if enabled)
  │
  ├─► Display success message
  │
  └─► Refresh listing page
END
```

## 5. Auction End Processing Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                  AUCTION END PROCESSING                         │
└─────────────────────────────────────────────────────────────────┘

START (Triggered by scheduled job when auction_end time reached)
  │
  ├─► System finds listing:
  │     • Status = ACTIVE
  │     • Sale type = AUCTION
  │     • auction_end <= now()
  │
  ├─► BEGIN TRANSACTION (Lock listing row)
  │
  ├─► Check if already processed:
  │     • Status = SOLD, EXPIRED, or CANCELLED?
  │
  ├─► [Already Processed?]
  │     │
  │     └─► ROLLBACK & END
  │
  ├─► Find winning bid:
  │     • Status = WINNING
  │     • Highest amount
  │
  ├─► [No Winning Bid?]
  │     │
  │     ├─► Set listing status = EXPIRED
  │     ├─► Notify seller: "Auction ended with no bids"
  │     ├─► Log: "Auction ended with no bids"
  │     └─► COMMIT & END
  │
  ├─► [Reserve Price Not Met?]
  │     │
  │     ├─► Set listing status = EXPIRED
  │     ├─► Mark all bids as LOST
  │     ├─► Notify seller: "Reserve not met"
  │     ├─► Log: "Auction ended, reserve not met"
  │     └─► COMMIT & END
  │
  ├─► [Reserve Met & Has Winning Bid?]
  │     │
  │     ├─► Mark winning bid status = WON
  │     │
  │     ├─► Mark other bids as LOST
  │     │
  │     ├─► Update listing:
  │     │     • winner_id = winning bidder
  │     │     • final_price = winning bid amount
  │     │     • Keep status = ACTIVE (will be hidden by escrow_id)
  │     │
  │     ├─► Create Escrow automatically:
  │     │     • seller_id = listing owner
  │     │     • buyer_id = winning bidder
  │     │     • amount = final_price
  │     │     • Calculate charges
  │     │     • status = ACCEPTED
  │     │     • Create conversation
  │     │
  │     ├─► Link listing to escrow:
  │     │     • listing.escrow_id = escrow.id
  │     │     • (This hides listing from marketplace)
  │     │
  │     ├─► Notify seller: "Auction won, escrow created"
  │     │
  │     ├─► Notify buyer: "You won the auction, proceed to payment"
  │     │
  │     ├─► Log auction completion:
  │     │     • Listing ID, winner ID
  │     │     • Final price
  │     │     • Escrow ID
  │     │
  │     └─► COMMIT TRANSACTION
  │
END
```

## 6. One-Time Sale (Buy Now) Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    ONE-TIME SALE (BUY NOW)                      │
└─────────────────────────────────────────────────────────────────┘

START
  │
  ├─► User views fixed-price listing
  │
  ├─► User clicks "Buy Now"
  │
  ├─► System checks:
  │     • Listing is active
  │     • Listing has buy_now_price > 0
  │     • User is not the seller
  │     • Listing not already sold or in escrow
  │
  ├─► [Checks Failed?]
  │     │
  │     └─► Display error: "Listing unavailable"
  │
  ├─► System calculates:
  │     • Total needed = buy_now_price + charges
  │     • Charges = (price * percent_charge) + fixed_charge
  │
  ├─► System checks user balance:
  │     • balance >= total_needed?
  │
  ├─► [Insufficient Balance?]
  │     │
  │     └─► Display error: "Insufficient balance"
  │         └─► Suggest deposit
  │
  ├─► BEGIN TRANSACTION (Lock listing row)
  │
  ├─► System processes purchase:
  │     • Create Bid record:
  │       - Status = WON
  │       - Amount = buy_now_price
  │       - is_buy_now = true
  │     • Mark other bids as LOST
  │     • Update listing:
  │       - winner_id = buyer
  │       - final_price = buy_now_price
  │       - current_bid = buy_now_price
  │       - Keep status = ACTIVE (hidden by escrow_id)
  │
  ├─► Create Escrow automatically:
  │     • seller_id = listing owner
  │     • buyer_id = buyer
  │     • amount = buy_now_price
  │     • Calculate charges (buyer pays)
  │     • status = ACCEPTED
  │     • Create conversation
  │
  ├─► Link listing to escrow:
  │     • listing.escrow_id = escrow.id
  │
  ├─► COMMIT TRANSACTION
  │
  ├─► Log purchase:
  │     • Listing ID, buyer ID
  │     • Amount, escrow ID
  │
  ├─► Notify seller: "Listing sold via Buy Now"
  │
  ├─► Notify buyer: "Purchase successful, proceed to payment"
  │
  ├─► Display success message
  │
  └─► Redirect to escrow details page
END
```

## 7. Offer System Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        OFFER SYSTEM                             │
└─────────────────────────────────────────────────────────────────┘

PART A: MAKING AN OFFER
────────────────────────
START
  │
  ├─► User views fixed-price listing
  │
  ├─► User clicks "Make Offer"
  │
  ├─► System checks:
  │     • Listing is active fixed-price
  │     • User is not the seller
  │     • User email & mobile verified
  │     • No existing pending offer from same user
  │
  ├─► User enters:
  │     • Offer amount (min 10% of asking price)
  │     • Optional message
  │
  ├─► System validates:
  │     • Offer >= 10% of asking price
  │     • Offer <= 110% of asking (warn if higher)
  │
  ├─► [Validation Failed?]
  │     │
  │     └─► Display error
  │
  ├─► System creates Offer:
  │     • Status = PENDING
  │     • Expires in 7 days
  │     • Log offer creation
  │
  ├─► Notify seller: "New offer received"
  │
  └─► Display success: "Offer submitted"
END

PART B: SELLER RESPONSE (Accept/Reject/Counter)
──────────────────────────────────────────────────
START
  │
  ├─► Seller views received offers
  │
  ├─► Seller selects action: Accept / Reject / Counter
  │
  ├─► [Action = ACCEPT]
  │     │
  │     ├─► System checks:
  │     │     • Offer not expired
  │     │     • Listing still available
  │     │     • Buyer has sufficient balance
  │     │
  │     ├─► BEGIN TRANSACTION (Lock offer & listing)
  │     │
  │     ├─► Reject all other pending offers
  │     │
  │     ├─► Update offer: Status = ACCEPTED
  │     │
  │     ├─► Update listing:
  │     │     • winner_id = buyer
  │     │     • final_price = offer amount
  │     │     • escrow_id = (will be set)
  │     │
  │     ├─► Create Escrow:
  │     │     • Status = ACCEPTED
  │     │     • Amount = offer amount
  │     │     • Calculate charges
  │     │
  │     ├─► Link listing to escrow
  │     │
  │     ├─► COMMIT TRANSACTION
  │     │
  │     ├─► Notify buyer: "Your offer was accepted"
  │     │
  │     └─► Redirect to escrow details
  │
  ├─► [Action = REJECT]
  │     │
  │     ├─► Update offer: Status = REJECTED
  │     ├─► Add rejection reason (optional)
  │     ├─► Notify buyer: "Your offer was rejected"
  │     └─► END
  │
  └─► [Action = COUNTER]
      │
      ├─► System validates:
      │     • Counter amount > original offer
      │     • Counter amount reasonable
      │
      ├─► Update offer:
      │     • Status = COUNTERED
      │     • counter_amount = new amount
      │     • counter_message = message
      │     • Expires in 7 days (new)
      │
      ├─► Notify buyer: "Seller made a counter offer"
      │
      └─► END

PART C: BUYER ACCEPTS COUNTER OFFER
────────────────────────────────────
START
  │
  ├─► Buyer views countered offer
  │
  ├─► Buyer clicks "Accept Counter Offer"
  │
  ├─► System checks:
  │     • Offer not expired
  │     • Listing still available
  │     • Buyer has sufficient balance
  │
  ├─► BEGIN TRANSACTION
  │
  ├─► Update offer: Status = ACCEPTED
  │
  ├─► Reject other offers
  │
  ├─► Create Escrow (same as Part B - Accept)
  │
  ├─► COMMIT TRANSACTION
  │
  ├─► Notify seller: "Counter offer accepted"
  │
  └─► Redirect to escrow details
END
```

## 8. Escrow Process Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      ESCROW PROCESS                             │
└─────────────────────────────────────────────────────────────────┘

PART A: ESCROW CREATION (Automatic)
────────────────────────────────────
START (Triggered by: Auction win, Buy Now, Offer acceptance)
  │
  ├─► System creates Escrow:
  │     • escrow_number = unique transaction ID
  │     • seller_id = listing owner
  │     • buyer_id = winner/buyer
  │     • creator_id = buyer
  │     • amount = final_price
  │     • Calculate charges (percent + fixed, capped)
  │     • buyer_charge = total charge (buyer pays)
  │     • seller_charge = 0
  │     • charge_payer = BUYER
  │     • status = ACCEPTED (auto-accepted)
  │     • title = "Purchase: [listing title]"
  │
  ├─► Create Conversation:
  │     • Link to escrow
  │     • buyer_id, seller_id
  │
  ├─► Link listing to escrow:
  │     • listing.escrow_id = escrow.id
  │     • (Hides listing from marketplace)
  │
  ├─► Log escrow creation
  │
  └─► Notify both parties: "Escrow created"
END

PART B: BUYER PAYMENT
──────────────────────
START
  │
  ├─► Buyer views escrow details
  │
  ├─► Buyer clicks "Pay Full Amount"
  │
  ├─► System calculates:
  │     • Total needed = amount + buyer_charge
  │     • Remaining = total - paid_amount
  │
  ├─► Buyer selects payment method:
  │     • Wallet balance OR
  │     • Payment gateway
  │
  ├─► [Method = Wallet]
  │     │
  │     ├─► Check balance >= remaining
  │     │
  │     ├─► [Insufficient?]
  │     │     │
  │     │     └─► Error: "Insufficient balance"
  │     │
  │     ├─► BEGIN TRANSACTION
  │     │
  │     ├─► Deduct from buyer balance
  │     │
  │     ├─► Update escrow:
  │     │     • paid_amount += remaining
  │     │
  │     ├─► Create transaction record
  │     │
  │     ├─► COMMIT TRANSACTION
  │     │
  │     ├─► Notify seller: "Escrow fully paid"
  │     │
  │     └─► Display success
  │
  └─► [Method = Gateway]
      │
      ├─► Create deposit session:
      │     • Type = escrow_full_payment
      │     • Amount = remaining
      │     • escrow_id stored
      │
      ├─► Redirect to payment gateway
      │
      ├─► [Payment Successful]
      │     │
      │     ├─► Gateway callback processes:
      │     │     • Add deposit to balance
      │     │     • Deduct escrow payment
      │     │     • Update escrow paid_amount
      │     │
      │     └─► Notify seller: "Escrow fully paid"
      │
      └─► [Payment Failed]
          │
          └─► Display error, return to escrow
END

PART C: SELLER RELEASES PAYMENT (Dispatch)
────────────────────────────────────────────
START
  │
  ├─► Seller views escrow details
  │
  ├─► Seller confirms transaction complete
  │
  ├─► Seller clicks "Release Payment"
  │
  ├─► System checks:
  │     • Escrow status = ACCEPTED
  │     • paid_amount >= (amount + buyer_charge)
  │     • Not already dispatched
  │
  ├─► [Checks Failed?]
  │     │
  │     └─► Display error
  │
  ├─► BEGIN TRANSACTION (Lock escrow & seller)
  │
  ├─► System processes dispatch:
  │     • Update escrow: status = COMPLETED
  │     • Add amount to seller balance
  │     • Deduct seller_charge (if any)
  │     • Create transaction records
  │     • Update listing: status = SOLD, sold_at = now
  │     • Update user stats:
  │       - seller: increment total_sales
  │       - buyer: increment total_purchases
  │
  ├─► COMMIT TRANSACTION
  │
  ├─► Log dispatch:
  │     • Escrow ID, amounts
  │     • Seller ID, buyer ID
  │     • Transaction IDs
  │
  ├─► Notify seller: "Payment dispatched"
  │
  └─► Display success: "Payment released"
END
```

## 9. Dispute Process Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      DISPUTE PROCESS                            │
└─────────────────────────────────────────────────────────────────┘

PART A: CREATING A DISPUTE
───────────────────────────
START
  │
  ├─► User (buyer or seller) views escrow details
  │
  ├─► User clicks "Open Dispute"
  │
  ├─► System checks:
  │     • User is buyer OR seller
  │     • Escrow status = ACCEPTED
  │     • paid_amount > 0 (cannot dispute unfunded escrow)
  │     • Not already disputed
  │     • Not completed or cancelled
  │     • Rate limit: max 5 disputes per 24h
  │
  ├─► [Checks Failed?]
  │     │
  │     └─► Display error with reason
  │
  ├─► User enters dispute reason (min 20 chars, max 2000)
  │
  ├─► System validates dispute reason
  │
  ├─► [Validation Failed?]
  │     │
  │     └─► Display error: "Please provide detailed reason"
  │
  ├─► System creates dispute:
  │     • Update escrow:
  │       - status = DISPUTED
  │       - disputer_id = user_id
  │       - dispute_note = reason
  │       - disputed_at = now()
  │     • Update conversation:
  │       - is_group = 1 (admin can join)
  │
  ├─► Log dispute creation:
  │     • Escrow ID, disputer ID
  │     • Amount, paid amount
  │     • Dispute reason length
  │     • IP address
  │
  ├─► Notify other party: "Escrow disputed"
  │
  ├─► Notify admin: "New dispute requires attention"
  │
  └─► Display success: "Dispute opened"
END

PART B: ADMIN RESOLUTION
─────────────────────────
START
  │
  ├─► Admin views disputed escrow
  │
  ├─► Admin reviews:
  │     • Dispute reason
  │     • Conversation messages
  │     • Escrow details
  │     • Payment history
  │
  ├─► Admin decides resolution:
  │     • Complete escrow (seller wins)
  │     • Cancel escrow (buyer wins)
  │     • Partial refund (split decision)
  │
  ├─► Admin enters:
  │     • buyer_amount (refund to buyer)
  │     • seller_amount (pay to seller)
  │     • status (COMPLETED or CANCELLED)
  │
  ├─► System validates:
  │     • buyer_amount + seller_amount <= paid_amount
  │     • dispute_charge = paid_amount - (buyer_amount + seller_amount)
  │
  ├─► [Validation Failed?]
  │     │
  │     └─► Error: "Amounts exceed paid amount"
  │
  ├─► BEGIN TRANSACTION
  │
  ├─► System processes resolution:
  │     • Update escrow: status = COMPLETED or CANCELLED
  │     • If buyer_amount > 0:
  │       - Add to buyer balance
  │       - Create transaction record
  │     • If seller_amount > 0:
  │       - Add to seller balance
  │       - Create transaction record
  │     • Update listing (if linked):
  │       - If CANCELLED: clear escrow_id, winner_id
  │       - If COMPLETED: status = SOLD
  │
  ├─► COMMIT TRANSACTION
  │
  ├─► Log resolution:
  │     • Escrow ID
  │     • Buyer amount, seller amount
  │     • Dispute charge
  │     • Final status
  │
  ├─► Notify buyer: "Dispute resolved"
  │
  ├─► Notify seller: "Dispute resolved"
  │
  ├─► Close conversation
  │
  └─► Display success: "Dispute resolved"
END
```

## 10. Deposit Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        DEPOSIT FLOW                             │
└─────────────────────────────────────────────────────────────────┘

PART A: INITIATING DEPOSIT
───────────────────────────
START
  │
  ├─► User clicks "Deposit Money"
  │     OR
  ├─► User redirected from checkout (escrow payment)
  │
  ├─► System displays:
  │     • Available payment gateways
  │     • Amount (if from checkout)
  │
  ├─► User selects:
  │     • Payment gateway
  │     • Currency
  │     • Amount (if not pre-filled)
  │
  ├─► System validates:
  │     • Gateway is active
  │     • Amount >= gateway min_amount
  │     • Amount <= gateway max_amount
  │
  ├─► [Validation Failed?]
  │     │
  │     └─► Display error with limits
  │
  ├─► System calculates:
  │     • Charge = fixed_charge + (amount * percent_charge)
  │     • Payable = amount + charge
  │     • Final amount = payable * rate
  │
  ├─► System creates Deposit record:
  │     • status = INITIATE
  │     • trx = unique transaction ID
  │     • Store escrow_id (if checkout)
  │     • Store milestone_id (if milestone payment)
  │
  ├─► Log deposit initiation:
  │     • Deposit ID, user ID
  │     • Amount, gateway, currency
  │     • Transaction ID
  │     • IP address
  │
  ├─► Store transaction ID in session
  │
  └─► Redirect to payment confirmation
END

PART B: PAYMENT PROCESSING
───────────────────────────
START
  │
  ├─► [Gateway Type = Automatic]
  │     │
  │     ├─► Redirect to gateway payment page
  │     │
  │     ├─► User completes payment on gateway
  │     │
  │     ├─► Gateway redirects to callback URL
  │     │
  │     ├─► System processes callback:
  │     │     • Verify payment status
  │     │     • Update deposit: status = SUCCESS
  │     │     • Add amount to user balance
  │     │     • Create transaction record
  │     │     • Handle escrow payment (if applicable)
  │     │     • Handle milestone payment (if applicable)
  │     │
  │     └─► Notify user: "Deposit successful"
  │
  └─► [Gateway Type = Manual]
      │
      ├─► Display manual payment instructions
      │
      ├─► User submits payment proof:
      │     • Transaction details
      │     • Screenshot/receipt
      │     • Additional info (per gateway)
      │
      ├─► System updates deposit:
      │     • status = PENDING
      │     • detail = payment proof data
      │
      ├─► Notify admin: "New deposit request"
      │
      ├─► Notify user: "Deposit request submitted"
      │
      └─► Wait for admin approval

PART C: ADMIN APPROVAL (Manual Only)
──────────────────────────────────────
START
  │
  ├─► Admin reviews deposit request
  │
  ├─► Admin verifies payment proof
  │
  ├─► [Admin Approves]
  │     │
  │     ├─► System processes:
  │     │     • Update deposit: status = SUCCESS
  │     │     • Add amount to user balance
  │     │     • Create transaction record
  │     │     • Handle escrow/milestone payment
  │     │
  │     ├─► Notify user: "Deposit approved"
  │     │
  │     └─► END
  │
  └─► [Admin Rejects]
      │
      ├─► System updates deposit: status = REJECTED
      │
      ├─► Notify user: "Deposit rejected"
      │
      └─► END
END
```

## 11. Withdrawal Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      WITHDRAWAL FLOW                            │
└─────────────────────────────────────────────────────────────────┘

PART A: INITIATING WITHDRAWAL
──────────────────────────────
START
  │
  ├─► User clicks "Withdraw Money"
  │
  ├─► System checks:
  │     • User KYC verified (if required)
  │     • User account active
  │
  ├─► [Checks Failed?]
  │     │
  │     └─► Display error: "KYC verification required"
  │
  ├─► System displays:
  │     • Available withdrawal methods
  │     • Method limits (min/max)
  │     • Charges per method
  │
  ├─► User selects:
  │     • Withdrawal method
  │     • Amount
  │
  ├─► System validates:
  │     • Amount >= method min_limit
  │     • Amount <= method max_limit
  │     • Amount <= user balance
  │
  ├─► System calculates:
  │     • Charge = fixed_charge + (amount * percent_charge)
  │     • After charge = amount - charge
  │     • Final amount = after_charge * rate
  │
  ├─► [After charge <= 0?]
  │     │
  │     └─► Error: "Amount too small after fees"
  │
  ├─► System creates Withdrawal record:
  │     • status = INITIATE
  │     • trx = unique transaction ID
  │     • Store all amounts and method info
  │
  ├─► Log withdrawal initiation:
  │     • Withdrawal ID, user ID
  │     • Amount, charge, final amount
  │     • Method, currency
  │     • User balance before
  │     • IP address
  │
  ├─► Store transaction ID in session
  │
  └─► Redirect to withdrawal preview
END

PART B: WITHDRAWAL PREVIEW & SUBMISSION
─────────────────────────────────────────
START
  │
  ├─► System displays preview:
  │     • Amount requested
  │     • Charges
  │     • Final amount to receive
  │     • Method details
  │
  ├─► System re-validates:
  │     • Balance still sufficient
  │     • Method still active
  │
  ├─► [Validation Failed?]
  │     │
  │     ├─► Delete withdrawal record
  │     └─► Error: "Please start over"
  │
  ├─► User fills withdrawal information:
  │     • Account details (per method)
  │     • 2FA code (if enabled)
  │
  ├─► System validates:
  │     • Withdrawal info format
  │     • 2FA code (if required)
  │
  ├─► [Validation Failed?]
  │     │
  │     └─► Display errors
  │
  ├─► BEGIN TRANSACTION
  │
  ├─► System processes withdrawal:
  │     • Update withdrawal: status = PENDING
  │     • Store withdrawal information
  │     • Deduct amount from user balance
  │     • Create transaction record (debit)
  │
  ├─► COMMIT TRANSACTION
  │
  ├─► Create admin notification
  │
  ├─► Notify user: "Withdrawal request submitted"
  │
  ├─► Log withdrawal submission:
  │     • Withdrawal ID
  │     • User balance after
  │
  └─► Redirect to withdrawal history
END

PART C: ADMIN PROCESSING
─────────────────────────
START
  │
  ├─► Admin views pending withdrawals
  │
  ├─► Admin reviews:
  │     • User account status
  │     • Withdrawal details
  │     • Payment information
  │     • Transaction history
  │
  ├─► [Admin Approves]
  │     │
  │     ├─► Admin processes payment:
  │     │     • Send funds via method
  │     │     • Record payment reference
  │     │
  │     ├─► System updates withdrawal:
  │     │     • status = APPROVED
  │     │     • admin_feedback = notes
  │     │
  │     ├─► Notify user: "Withdrawal approved & processed"
  │     │
  │     └─► END
  │
  └─► [Admin Rejects]
      │
      ├─► System updates withdrawal:
      │     • status = REJECTED
      │     • admin_feedback = reason
      │
      ├─► BEGIN TRANSACTION
      │
      ├─► Refund to user balance:
      │     • Add amount back to balance
      │     • Create transaction record (credit)
      │
      ├─► COMMIT TRANSACTION
      │
      ├─► Notify user: "Withdrawal rejected, funds refunded"
      │
      └─► END
END
```

---

## Summary

This document contains comprehensive flowcharts for all major features of the marketplace system:

1. **Account Creation** - User registration with validation and auto-login
2. **Login** - Authentication with security checks and logging
3. **Creating a Listing** - Multi-step listing creation with validation
4. **Auction Bidding** - Real-time bidding with anti-sniping protection
5. **Auction End Processing** - Automated auction completion and escrow creation
6. **One-Time Sale (Buy Now)** - Instant purchase flow
7. **Offer System** - Make, accept, reject, and counter offers
8. **Escrow Process** - Payment handling and release
9. **Dispute Process** - Dispute creation and admin resolution
10. **Deposit Flow** - Adding funds via gateways
11. **Withdrawal Flow** - Requesting and processing withdrawals

### Key Features Documented:
- ✅ Transaction safety (database locks, transactions)
- ✅ Rate limiting and security checks
- ✅ Comprehensive logging at critical points
- ✅ Error handling and validation
- ✅ Notification system
- ✅ Admin intervention points
- ✅ Status transitions
- ✅ Financial calculations and charges

### Critical Processes Logged:
- User registration and login
- Listing creation and updates
- Bid placement and auction completion
- Offer creation and acceptance
- Escrow creation, payment, and dispatch
- Dispute creation and resolution
- Deposit and withdrawal transactions

All flowcharts follow the actual implementation in the codebase and include error handling, validation, and logging points.

