 SocietyPress Project Overview                                                 
                                                                                
  Purpose                                                                       
                                                                                
  A commercial WordPress plugin for membership management in genealogical       
  societies, historical societies, and heritage organizations. It differentiates
   from generic plugins with specialized genealogy fields (surnames researched, 
  research areas) and governance tracking (committees, officer positions).      
                                                                                
  Target Market: Organizations needing genealogy-specific features, sold at
  stricklindevelopment.com/studiopress/societypress.                                            
                                                                                
  ---                                                                           
  Paths                                                                         
  Location: Source (Git)                                                        
  Path: ~/Documents/Development/Web/WordPress/SocietyPress/                     
  ────────────────────────────────────────                                      
  Location: Plugin Code                                                         
  Path: ~/Documents/Development/Web/WordPress/SocietyPress/plugin/              
  ────────────────────────────────────────                                      
  Location: XAMPP Testing                                                       
  Path: /Applications/XAMPP/xamppfiles/htdocs/cms/wp-content/plugins/societypres
  s-core/                                                                       
  ────────────────────────────────────────                                      
  Location: GitHub                                                              
  Path: https://github.com/charles-stricklin/SocietyPress                       
  ---                                                                           
  Environment                                                                   
                                                                                
  - WordPress 6.0+, PHP 8.0+, MySQL 5.7+                                        
  - 14 custom database tables with sp_ prefix                                   
  - AES-256-GCM encryption for sensitive data                                   
  - ~5,700 lines of PHP across OOP classes                                      
  - Deploy to XAMPP via manual copy for testing                                 
                                                                                
  ---                                                                           
  Completed Work                                                                
                                                                                
  - Full member CRUD with multiple statuses                                     
  - Membership tier management                                                  
  - CSV import (Wild Apricot compatible) with intelligent field mapping         
  - CSV export respecting filters                                               
  - WP_List_Table admin interface (sortable, searchable, filterable)            
  - Bulk actions with "select all across pages"                                 
  - Genealogy fields (surnames, research areas, service profiles)               
  - Encryption, audit logging, capability checks                                
  - PHP 8 compatibility fixes                                                   
  - Centralized settings page                                                   
                                                                                
  ---                                                                           
  Plans (from TO-DO.md)                                                         
                                                                                
  Short-Term (MVP):                                                             
  - License validation system                                                   
  - Public member directory (shortcode)                                         
  - Member self-service portal                                                  
  - Email notifications (welcome, renewal reminders)                            
  - Dashboard widgets                                                           
                                                                                
  Medium-Term:                                                                  
  - Payment gateway integration (Stripe, PayPal)                                
  - Committee/officer management UI                                             
  - Query builder / advanced search                                             
  - Surname connection engine ("Members also researching...")                   
  - GEDCOM import                                                               
                                                                                
  Long-Term:                                                                    
  - REST API                                                                    
  - Zapier/webhooks                                                             
  - Mobile app companion                                                        
  - Event management integration                                                
                                                                                
  ---                                                                           
  Technical Debt                                                                
                                                                                
  - Unit/integration tests needed                                               
  - i18n .pot file generation                                                   
  - Accessibility audit                                                         
  - PHPDoc documentation                                                        
  - Performance optimization for large datasets                                 
                                                                                
  ---                                                                           
  Known Issues                                                                  
                                                                                
  None currently tracked.