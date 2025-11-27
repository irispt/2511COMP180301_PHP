<?php
/**
 * Chatbot Widget HTML Component
 * This file contains the HTML structure for the chatbot widget
 * Include this in your main layout pages
 */

function renderChatbotWidget() {
    ?>
    <!-- Chatbot Toggle Button -->
    <button id="chatbot-toggle" title="Chat with us!" aria-label="Open chat">
        💬
    </button>

    <!-- Chatbot Container -->
    <div id="chatbot-container" role="dialog" aria-label="Chatbot">
        <!-- Header -->
        <div class="chatbot-header">
            <h3>🤖 Assistant</h3>
            <button class="chatbot-header-close" id="chatbot-close" aria-label="Close chat">
                ✕
            </button>
        </div>

        <!-- Messages Area -->
        <div class="chatbot-messages" id="chatbot-messages" role="log" aria-live="polite">
            <!-- Messages will be added here dynamically -->
            <div class="message bot">
                <div class="message-content">
                    Xin chào! 👋 Tôi có thể giúp bạn với những gì?<br>
                    (Hello! How can I help you?)
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="chatbot-input-area">
            <input 
                type="text" 
                id="chatbot-input" 
                placeholder="Type your message..." 
                aria-label="Message input"
            />
            <button id="chatbot-send" aria-label="Send message">
                ➤
            </button>
        </div>
    </div>
    <?php
}
?>
