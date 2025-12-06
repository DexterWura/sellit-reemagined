/**
 * Listing Form Handler
 * Modular JavaScript for listing creation form
 */
const ListingFormHandler = {
    currentStep: 1,
    totalSteps: 6, // Updated from 4 to 6 steps
    autoSaveTimer: null,
    autoSaveDelay: 2000, // 2 seconds after user stops typing
    selectedFiles: [],
    config: {
        draftSaveUrl: null,
        draftClearUrl: null,
        maxImages: 10,
        hasDraft: false,
        currentStage: 1
    },

    // Cache DOM selectors
    $form: null,
    $steps: null,
    $imageInput: null,
    $imagePreview: null,
    $uploadArea: null,

    /**
     * Initialize the form handler
     */
    init: function(config) {
        this.config = Object.assign(this.config, config || {});
        this.currentStep = this.config.currentStage || 1;
        
        this.cacheSelectors();
        this.initStepper();
        this.initDraftSaving();
        this.initFieldToggles();
        this.initMediaUploader();
        this.initDomainPreview();
        
        // Restore stage on page load
        if (this.currentStep > 1) {
            this.showStep(this.currentStep);
            if (this.config.hasDraft) {
                setTimeout(() => {
                    if (typeof notify === 'function') {
                        notify('info', this.translate('Draft restored. Your previous progress has been loaded.'));
                    }
                }, 500);
            }
        }
        
        // Initialize domain preview if domain type is selected and has value
        setTimeout(() => {
            const businessType = $('input[name="business_type"]:checked').val();
            if (businessType === 'domain') {
                const domainValue = $('#domainNameInput').val();
                if (domainValue && domainValue.trim()) {
                    this.updateDomainCardPreview();
                }
            }
        }, 200);
    },

    /**
     * Cache frequently used DOM selectors
     */
    cacheSelectors: function() {
        this.$form = $('#listingForm');
        this.$steps = $('.form-step');
        this.$imageInput = document.getElementById('imageInput');
        this.$imagePreview = document.getElementById('imagePreview');
        this.$uploadArea = document.getElementById('uploadArea');
    },

    /**
     * Initialize step navigation
     */
    initStepper: function() {
        const self = this;
        
        // Next button handler
        $(document).on('click', '.btn-next', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const nextStep = parseInt($(this).data('next'));
            
            if (!nextStep || isNaN(nextStep)) {
                if (typeof notify === 'function') {
                    notify('error', 'Invalid step configuration');
                }
                return false;
            }
            
            // Handle submit button
            if ($(this).data('next') === 'submit' || $(this).attr('type') === 'submit') {
                self.$form.submit();
                return false;
            }
            
            // Validate current step before proceeding
            if (self.validateStep(self.currentStep)) {
                // Special handling for Step 2 to Step 3: Skip Step 3 if verification not required
                if (self.currentStep === 2 && nextStep === 3) {
                    const businessType = $('input[name="business_type"]:checked').val();
                    const requiresValidation = ['domain', 'website', 'social_media_account'];
                    
                    if (!requiresValidation.includes(businessType)) {
                        // Skip Step 3, go directly to Step 4
                        self.showStep(4);
                        self.saveDraft();
                        return false;
                    }
                }
                
                self.showStep(nextStep);
                self.saveDraft();
                
                // If moving to Step 3, trigger ownership validation check
                if (nextStep === 3) {
                    if (typeof ownershipValidation !== 'undefined') {
                        // Update business type and asset URL before checking
                        ownershipValidation.businessType = $('input[name="business_type"]:checked').val();
                        ownershipValidation.primaryAssetUrl = ownershipValidation.getCurrentAssetUrl();
                        
                        setTimeout(function() {
                            ownershipValidation.checkIfValidationRequired();
                            ownershipValidation.restoreValidationState();
                        }, 200);
                    }
                }
            } else {
                // Validation failed - don't proceed
                return false;
            }
            
            return false;
        });
        
        // Previous button handler
        $(document).on('click', '.btn-prev', function() {
            const prevStep = parseInt($(this).data('prev'));
            if (prevStep && !isNaN(prevStep)) {
                self.showStep(prevStep);
                self.saveDraft();
            }
        });
    },

    /**
     * Show a specific step
     */
    showStep: function(step) {
        step = parseInt(step);
        
        if (isNaN(step) || step < 1 || step > this.totalSteps) {
            console.error('Invalid step number:', step);
            return;
        }
        
        // Remove required attribute from all fields in hidden steps to prevent HTML5 validation errors
        $('.form-step.d-none [required]').each(function() {
            $(this).attr('data-was-required', 'true').removeAttr('required');
        });
        
        // Hide all steps
        this.$steps.addClass('d-none');
        
        // Show the target step
        const targetStep = this.$steps.filter(function() {
            return parseInt($(this).attr('data-step')) === step;
        });
        
        if (targetStep.length === 0) {
            console.error('Step not found:', step);
            return;
        }
        
        targetStep.removeClass('d-none');
        
        // Restore required attribute for fields in the visible step
        targetStep.find('[data-was-required="true"]').each(function() {
            $(this).attr('required', 'required').removeAttr('data-was-required');
        });
        
        // Update progress indicator
        this.updateProgress(step);
        
        this.currentStep = step;
        window.scrollTo({top: 0, behavior: 'smooth'});
        
        // Auto-save when changing steps
        this.saveDraft();
    },

    /**
     * Update progress indicator
     */
    updateProgress: function(currentStep) {
        $('.progress-steps .step').removeClass('active completed');
        $('.progress-steps .step').each(function() {
            const stepNum = parseInt($(this).attr('data-step'));
            if (!isNaN(stepNum)) {
                if (stepNum < currentStep) {
                    $(this).addClass('completed');
                } else if (stepNum === currentStep) {
                    $(this).addClass('active');
                }
            }
        });
    },

    /**
     * Validate a specific step
     */
    validateStep: function(step) {
        if (step === 1) {
            return this.validateStep1();
        } else if (step === 2) {
            return this.validateStep2();
        } else if (step === 3) {
            return this.validateStep3();
        } else if (step === 4) {
            return this.validateStep4();
        } else if (step === 5) {
            return this.validateStep5();
        }
        // Step 6 (Media) has no required validation
        return true;
    },

    /**
     * Validate Step 1: Business Type Selection Only
     */
    validateStep1: function() {
        const businessType = $('input[name="business_type"]:checked').val();
        if (!businessType) {
            if (typeof notify === 'function') {
                notify('error', this.translate('Please select a business type'));
            }
            return false;
        }
        return true;
    },

    /**
     * Validate Step 2: Asset Entry
     */
    validateStep2: function() {
        const businessType = $('input[name="business_type"]:checked').val();
        
        if (businessType === 'domain') {
            const domainInput = document.getElementById('domainNameInput');
            if (!domainInput || !domainInput.value || !domainInput.value.trim()) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Please enter a domain name'));
                }
                if (domainInput) domainInput.focus();
                return false;
            }
            if (!domainInput.value.trim().match(/^https?:\/\//i)) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Domain must start with http:// or https://'));
                }
                domainInput.focus();
                return false;
            }
        } else if (businessType === 'website') {
            const websiteInput = document.getElementById('websiteUrlInput');
            if (!websiteInput || !websiteInput.value || !websiteInput.value.trim()) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Please enter a website URL'));
                }
                if (websiteInput) websiteInput.focus();
                return false;
            }
            if (!websiteInput.value.trim().match(/^https?:\/\//i)) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Website URL must start with http:// or https://'));
                }
                websiteInput.focus();
                return false;
            }
        } else if (businessType === 'social_media_account') {
            const platform = $('select[name="platform"]').val();
            const username = $('input[name="social_username"]').val();
            if (!platform || !platform.trim()) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Please select a platform'));
                }
                return false;
            }
            if (!username || !username.trim()) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Please enter a username/handle'));
                }
                $('input[name="social_username"]').focus();
                return false;
            }
        } else if (businessType === 'mobile_app' || businessType === 'desktop_app') {
            const appName = $('input[name="app_name"]').val();
            if (!appName || !appName.trim()) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Please enter an app name'));
                }
                $('input[name="app_name"]').focus();
                return false;
            }
        }
        
        return true;
    },

    /**
     * Validate Step 3: Ownership Verification (Conditional)
     */
    validateStep3: function() {
        const businessType = $('input[name="business_type"]:checked').val();
        const requiresVerification = ['domain', 'website', 'social_media_account'];
        
        // Skip validation for types that don't require verification
        if (!requiresVerification.includes(businessType)) {
            return true;
        }
        
        // Check if ownership is verified (check global ownershipValidation object if available)
        if (typeof ownershipValidation !== 'undefined' && ownershipValidation.isVerified) {
            return true;
        }
        
        // Check session verification status
        // This will be handled by the ownership validation handler
        if (typeof notify === 'function') {
            notify('error', this.translate('Please verify ownership before continuing'));
        }
        return false;
    },

    /**
     * Validate Step 4: Business Details
     */
    validateStep4: function() {
        const description = $('textarea[name="description"]').val();
        if (!description || !description.trim()) {
            if (typeof notify === 'function') {
                notify('error', this.translate('Please enter a description'));
            }
            $('textarea[name="description"]').focus();
            return false;
        }
        
        // Check minimum length from data attribute (set by admin)
        const $descriptionField = $('textarea[name="description"]');
        const minLength = parseInt($descriptionField.data('min-length') || 100);
        if (description.trim().length < minLength) {
            if (typeof notify === 'function') {
                notify('error', this.translate('Description must be at least ' + minLength + ' characters'));
            }
            $descriptionField.focus();
            return false;
        }
        
        return true;
    },

    /**
     * Validate Step 5: Pricing
     */
    validateStep5: function() {
        const saleType = $('input[name="sale_type"]:checked').val();
        if (!saleType) {
            if (typeof notify === 'function') {
                notify('error', this.translate('Please select a sale type'));
            }
            return false;
        }
        
        if (saleType === 'fixed_price') {
            const askingPrice = $('input[name="asking_price"]').val();
            if (!askingPrice || parseFloat(askingPrice) <= 0) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Please enter a valid asking price'));
                }
                $('input[name="asking_price"]').focus();
                return false;
            }
        } else if (saleType === 'auction') {
            const startingBid = $('input[name="starting_bid"]').val();
            if (!startingBid || parseFloat(startingBid) <= 0) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Please enter a valid starting bid'));
                }
                $('input[name="starting_bid"]').focus();
                return false;
            }
            
            const auctionDuration = $('select[name="auction_duration"]').val();
            if (!auctionDuration) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Please select an auction duration'));
                }
                return false;
            }
        }
        
        return true;
    },

    /**
     * Initialize draft saving functionality
     */
    initDraftSaving: function() {
        const self = this;
        
        // Auto-save on form field changes
        this.$form.on('input change', 'input, textarea, select', function() {
            self.saveDraft();
        });
        
        // Clear draft button
        $('#clearDraftBtn').on('click', function() {
            if (confirm(self.translate('Are you sure you want to clear the draft? All unsaved data will be lost.'))) {
                self.clearDraft();
            }
        });
        
        // Save draft before form submission
        this.$form.on('submit', function(e) {
            clearTimeout(self.autoSaveTimer);
            self.saveDraft();
            
            const submitBtn = $(this).find('button[type="submit"]');
            if (submitBtn.length && !submitBtn.prop('disabled')) {
                submitBtn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>' + self.translate('Submitting...'));
            }
        });
    },

    /**
     * Save draft using FormData for file handling
     */
    saveDraft: function() {
        const self = this;
        clearTimeout(this.autoSaveTimer);
        
        this.autoSaveTimer = setTimeout(function() {
            const formData = new FormData();
            formData.append('current_stage', self.currentStep);
            formData.append('_token', $('input[name="_token"]').val());
            
            // Get all form inputs (skip files for draft)
            self.$form.find('input, textarea, select').each(function() {
                const $field = $(this);
                const name = $field.attr('name');
                const type = $field.attr('type');
                
                if (!name || name === '_token' || name === 'images[]') {
                    return;
                }
                
                if (type === 'checkbox' || type === 'radio') {
                    if ($field.is(':checked')) {
                        formData.append(name, $field.val());
                    }
                } else if (type === 'file') {
                    // Skip file inputs for draft
                    return;
                } else {
                    formData.append(name, $field.val() || '');
                }
            });
            
            if (!self.config.draftSaveUrl) {
                console.error('Draft save URL not configured');
                return;
            }
            
            $.ajax({
                url: self.config.draftSaveUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        if ($('.draft-indicator').length === 0) {
                            $('.card-header').find('h5').after(`
                                <div class="draft-indicator">
                                    <span class="badge bg-info">
                                        <i class="las la-save me-1"></i>
                                        ${self.translate('Draft Saved')}
                                    </span>
                                </div>
                            `);
                        }
                    }
                },
                error: function() {
                    console.error('Failed to save draft');
                }
            });
        }, this.autoSaveDelay);
    },

    /**
     * Clear draft
     */
    clearDraft: function() {
        const self = this;
        
        if (!this.config.draftClearUrl) {
            console.error('Draft clear URL not configured');
            return;
        }
        
        $.ajax({
            url: this.config.draftClearUrl,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $('.draft-indicator').remove();
                    if (typeof notify === 'function') {
                        notify('success', self.translate('Draft cleared successfully'));
                    }
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            }
        });
    },

    /**
     * Initialize field toggles and business type handlers
     */
    initFieldToggles: function() {
        const self = this;
        
        // Business type change handler
        $('input[name="business_type"]').on('change', function() {
            const type = $(this).val();
            self.handleBusinessTypeChange(type);
        });
        
        // Sale type change handler
        $('input[name="sale_type"]').on('change', function() {
            const type = $(this).val();
            if (type === 'auction') {
                $('.fixed-price-fields').addClass('d-none');
                $('.auction-fields').removeClass('d-none');
            } else {
                $('.fixed-price-fields').removeClass('d-none');
                $('.auction-fields').addClass('d-none');
            }
        });
        
        // Initialize sale type
        const savedSaleType = $('input[name="sale_type"]:checked').val();
        if (savedSaleType) {
            $('input[name="sale_type"]:checked').trigger('change');
        } else {
            $('input[name="sale_type"][value="fixed_price"]').trigger('change');
        }
        
        // Confidential & NDA toggle
        $('#isConfidential').on('change', function() {
            if ($(this).is(':checked')) {
                $('#ndaSection').slideDown();
                $('#confidentialReasonSection').slideDown();
            } else {
                $('#ndaSection').slideUp();
                $('#confidentialReasonSection').slideUp();
                $('#requiresNda').prop('checked', false);
            }
        });
        
        // Initialize confidential section if pre-checked
        if ($('#isConfidential').is(':checked')) {
            $('#ndaSection').show();
            $('#confidentialReasonSection').show();
        }
        
        // Initialize business type if pre-selected
        const preselectedType = $('input[name="business_type"]:checked').val();
        if (preselectedType) {
            this.handleBusinessTypeChange(preselectedType, true);
            $('#step1ContinueBtn').prop('disabled', false);
        } else {
            $('.financial-section').addClass('d-none');
            $('.domain-info-message').addClass('d-none');
            $('#step1ContinueBtn').prop('disabled', false);
        }
        
        // If restoring from draft, trigger business type change
        if (this.currentStep > 1 && preselectedType) {
            setTimeout(() => {
                $('input[name="business_type"]:checked').trigger('change');
            }, 100);
        }
        
        // Domain and website input handlers
        $('#domainNameInput').on('input', function() {
            self.handleDomainInput($(this).val());
        });
        
        $('#websiteUrlInput').on('input', function() {
            self.handleWebsiteInput($(this).val());
        });
        
        // Auto-prepend protocol on blur
        $('#domainNameInput').on('blur', function() {
            self.autoPrependProtocol(this, 'domain');
            // Update preview after protocol is added
            setTimeout(() => {
                self.updateDomainCardPreview();
            }, 100);
        });
        
        $('#websiteUrlInput').on('blur', function() {
            self.autoPrependProtocol(this, 'website');
        });
    },

    /**
     * Handle business type change
     */
    handleBusinessTypeChange: function(type, isInitial = false) {
        // Hide all business-specific field sections
        $('.business-fields').addClass('d-none');
        $('#domainInputSection').hide();
        $('#websiteInputSection').hide();
        
        // Show relevant input section based on business type
        if (type === 'domain') {
            $('#domainInputSection').show();
            $('#domainNameInput').attr('required', 'required');
            $('.financial-section').addClass('d-none');
            $('.domain-info-message').removeClass('d-none');
            $('.image-upload-section').addClass('d-none');
            
            if (isInitial) {
                const domainValue = $('#domainNameInput').val();
                if (domainValue && domainValue.trim()) {
                    // Update preview immediately
                    this.updateDomainCardPreview();
                    // Also trigger input event to ensure everything is synced
                    setTimeout(() => {
                        $('#domainNameInput').trigger('input');
                    }, 100);
                } else {
                    // Even if no value, update preview to show default
                    setTimeout(() => {
                        this.updateDomainCardPreview();
                    }, 100);
                }
            }
        } else if (type === 'website') {
            $('#websiteInputSection').show();
            $('#websiteUrlInput').attr('required', 'required');
            
            if (isInitial) {
                const websiteValue = $('#websiteUrlInput').val();
                if (websiteValue && websiteValue.match(/^https?:\/\//i)) {
                    setTimeout(() => {
                        $('#websiteUrlInput').trigger('input');
                    }, 300);
                }
            }
        } else if (type === 'social_media_account') {
            $('.social_media_account-fields').removeClass('d-none');
        } else if (type === 'mobile_app') {
            $('.mobile_app-fields').removeClass('d-none');
        } else if (type === 'desktop_app') {
            $('.desktop_app-fields').removeClass('d-none');
        }
        
        // Filter categories
        $('#listingCategory option').each(function() {
            const optionType = $(this).data('type');
            if (!optionType || optionType === type) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        $('#listingCategory').val('');
        
        // Update domain preview if domain type
        if (type === 'domain') {
            setTimeout(() => {
                this.updateDomainCardPreview();
            }, 100);
        }
    },

    /**
     * Handle domain input changes
     */
    handleDomainInput: function(value) {
        const trimmed = value.trim();
        const warning = $('#domainProtocolWarning');
        const helpText = $('#domainHelpText');
        
        // Always update preview, even if protocol is missing (will show the domain name)
        this.updateDomainCardPreview();
        
        if (trimmed && !trimmed.match(/^https?:\/\//i)) {
            warning.slideDown();
            $('#domainNameInput').addClass('is-invalid border-warning');
            helpText.html('<span class="text-danger"><i class="las la-exclamation-circle"></i> ' + 
                this.translate('URL must start with http:// or https://') + '</span>');
        } else {
            warning.slideUp();
            $('#domainNameInput').removeClass('is-invalid border-warning');
            helpText.html(this.translate('Enter domain with http:// or https:// (e.g., https://example.com)'));
        }
        
        $('#step1ContinueBtn').prop('disabled', false);
    },

    /**
     * Handle website input changes
     */
    handleWebsiteInput: function(value) {
        const trimmed = value.trim();
        const warning = $('#websiteProtocolWarning');
        const helpText = $('#websiteHelpText');
        
        if (trimmed && !trimmed.match(/^https?:\/\//i)) {
            warning.slideDown();
            $('#websiteUrlInput').addClass('is-invalid border-warning');
            helpText.html('<span class="text-danger"><i class="las la-exclamation-circle"></i> ' + 
                this.translate('URL must start with http:// or https://') + '</span>');
        } else {
            warning.slideUp();
            $('#websiteUrlInput').removeClass('is-invalid border-warning');
            helpText.html(this.translate('Enter full URL starting with http:// or https://'));
        }
        
        $('#step1ContinueBtn').prop('disabled', false);
    },

    /**
     * Auto-prepend protocol if missing
     */
    autoPrependProtocol: function(input, type) {
        let value = $(input).val().trim();
        if (value && !value.match(/^https?:\/\//i)) {
            if (value.match(/^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}/)) {
                $(input).val('https://' + value);
                $(input).trigger('input');
            }
        }
    },

    /**
     * Initialize domain card preview
     */
    initDomainPreview: function() {
        const self = this;
        
        // Update card when price changes
        $('input[name="asking_price"], input[name="starting_bid"]').on('input', function() {
            if ($('input[name="business_type"]:checked').val() === 'domain') {
                self.updateDomainCardPreview();
            }
        });
        
        $('input[name="sale_type"]').on('change', function() {
            if ($('input[name="business_type"]:checked').val() === 'domain') {
                self.updateDomainCardPreview();
            }
        });
    },

    /**
     * Generate domain color based on domain name
     */
    getDomainColor: function(domain) {
        if (!domain) return ['#667eea', '#764ba2'];
        
        let hash = 0;
        for (let i = 0; i < domain.length; i++) {
            hash = domain.charCodeAt(i) + ((hash << 5) - hash);
        }
        
        const gradients = [
            ['#667eea', '#764ba2'], // Purple
            ['#f093fb', '#f5576c'], // Pink
            ['#4facfe', '#00f2fe'], // Blue
            ['#43e97b', '#38f9d7'], // Green
            ['#fa709a', '#fee140'], // Pink-Yellow
            ['#30cfd0', '#330867'], // Cyan-Purple
            ['#a8edea', '#fed6e3'], // Light Blue-Pink
            ['#ff9a9e', '#fecfef'], // Red-Pink
            ['#ffecd2', '#fcb69f'], // Orange
            ['#ff6e7f', '#bfe9ff'], // Red-Blue
        ];
        
        const index = Math.abs(hash) % gradients.length;
        return gradients[index];
    },

    /**
     * Update domain card preview
     */
    updateDomainCardPreview: function() {
        // Only update if domain type is selected
        const businessType = $('input[name="business_type"]:checked').val();
        if (businessType !== 'domain') {
            return;
        }
        
        const domainInputEl = document.getElementById('domainNameInput');
        let domainValue = '';
        
        if (domainInputEl) {
            domainValue = domainInputEl.value || '';
        } else {
            domainValue = $('#domainNameInput').val() || '';
        }
        
        if (domainValue && domainValue.trim()) {
            const trimmedValue = domainValue.trim();
            // Extract domain name - remove protocol, www, and path
            let domainName = trimmedValue.replace(/^https?:\/\//i, '').replace(/^www\./i, '').split('/')[0].split('?')[0].split('#')[0];
            domainName = domainName.trim();
            
            // If still empty after cleaning, try to extract from the original value
            if (!domainName) {
                domainName = trimmedValue;
            }
            
            const displayName = domainName || 'example.com';
            
            // Update preview elements
            if ($('#domainNamePreview').length) {
                $('#domainNamePreview').text(displayName);
            }
            if ($('#domainTitlePreview').length) {
                $('#domainTitlePreview').text(displayName);
            }
            
            // Update price preview
            const saleType = $('input[name="sale_type"]:checked').val();
            let price = '0.00';
            if (saleType === 'fixed_price') {
                price = $('input[name="asking_price"]').val() || '0.00';
            } else if (saleType === 'auction') {
                price = $('input[name="starting_bid"]').val() || '0.00';
            }
            if ($('#domainPricePreview').length) {
                $('#domainPricePreview').text('$' + parseFloat(price).toFixed(2) + ' USD');
            }
            
            // Update background color
            const colors = this.getDomainColor(domainName);
            if ($('#domainCardImage').length) {
                $('#domainCardImage').css('background', `linear-gradient(135deg, ${colors[0]} 0%, ${colors[1]} 100%)`);
            }
        } else {
            // Only show default if no value entered
            if ($('#domainNamePreview').length) {
                $('#domainNamePreview').text('example.com');
            }
            if ($('#domainTitlePreview').length) {
                $('#domainTitlePreview').text(this.translate('Domain Name'));
            }
        }
    },

    /**
     * Initialize media uploader
     */
    initMediaUploader: function() {
        const self = this;
        
        if (!this.$imageInput || !this.$uploadArea) {
            return;
        }
        
        // File input change handler
        this.$imageInput.addEventListener('change', function(e) {
            self.handleFiles(e.target.files);
        });
        
        // Drag and drop handlers
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.$uploadArea.addEventListener(eventName, this.preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            this.$uploadArea.addEventListener(eventName, () => {
                self.$uploadArea.classList.add('drag-over');
            });
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            this.$uploadArea.addEventListener(eventName, () => {
                self.$uploadArea.classList.remove('drag-over');
            });
        });
        
        this.$uploadArea.addEventListener('drop', function(e) {
            self.handleFiles(e.dataTransfer.files);
        });
        
        // Remove button handler
        $(document).on('click', '.remove-btn', function() {
            const index = $(this).data('index');
            self.selectedFiles.splice(index, 1);
            self.rebuildPreview();
            self.updateFileInput();
        });
    },

    /**
     * Prevent default drag and drop behavior
     */
    preventDefaults: function(e) {
        e.preventDefault();
        e.stopPropagation();
    },

    /**
     * Handle file selection
     */
    handleFiles: function(files) {
        const maxFiles = this.config.maxImages || 10;
        
        Array.from(files).forEach(file => {
            if (this.selectedFiles.length >= maxFiles) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Maximum') + ' ' + maxFiles + ' ' + this.translate('images allowed'));
                }
                return;
            }
            
            if (!file.type.startsWith('image/')) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('Only image files are allowed'));
                }
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                if (typeof notify === 'function') {
                    notify('error', this.translate('File size must be less than 2MB'));
                }
                return;
            }
            
            this.selectedFiles.push(file);
            this.displayPreview(file, this.selectedFiles.length - 1);
        });
        
        this.updateFileInput();
    },

    /**
     * Display file preview
     */
    displayPreview: function(file, index) {
        const self = this;
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-btn" data-index="${index}">
                    <i class="las la-times"></i>
                </button>
            `;
            self.$imagePreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    },

    /**
     * Rebuild preview after file removal
     */
    rebuildPreview: function() {
        this.$imagePreview.innerHTML = '';
        this.selectedFiles.forEach((file, index) => {
            this.displayPreview(file, index);
        });
    },

    /**
     * Update file input with selected files
     */
    updateFileInput: function() {
        const dt = new DataTransfer();
        this.selectedFiles.forEach(file => dt.items.add(file));
        this.$imageInput.files = dt.files;
    },

    /**
     * Simple translation helper (fallback if Laravel's @lang is not available)
     */
    translate: function(text) {
        // This is a fallback - in practice, translations should be handled server-side
        return text;
    }
};

