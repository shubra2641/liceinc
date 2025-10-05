// Frontend JavaScript
// This file compiles all frontend-specific JavaScript functionality

// Import Bootstrap JS components
import 'bootstrap';

// Import any frontend libraries
// You can add your frontend JS libraries here

console.log('Frontend app.js loaded successfully');

// Frontend specific functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize frontend-specific features
    console.log('Frontend DOM content loaded');
    
    // Add any frontend-specific JavaScript initialization here
    initializeFrontendFeatures();
});

function initializeFrontendFeatures() {
    // Frontend specific JavaScript features
    console.log('Frontend features initialized');
    
    // Add smooth scrolling
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // You can add more frontend-specific functions here
}