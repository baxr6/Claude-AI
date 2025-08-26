-- Claude Chat Module Installation Script
-- For PHP-Nuke Evo Extreme

-- Chat messages table
CREATE TABLE IF NOT EXISTS `nuke_claude_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sender` enum('user','claude') NOT NULL,
  `timestamp` int(11) NOT NULL,
  `session_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `timestamp` (`timestamp`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Configuration table
CREATE TABLE IF NOT EXISTS `nuke_claude_config` (
  `config_name` varchar(50) NOT NULL,
  `config_value` text NOT NULL,
  PRIMARY KEY (`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Error logs table
CREATE TABLE IF NOT EXISTS `nuke_claude_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_type` enum('error','info','warning') NOT NULL DEFAULT 'info',
  `message` text NOT NULL,
  `timestamp` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `log_type` (`log_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- User preferences table
CREATE TABLE IF NOT EXISTS `nuke_claude_preferences` (
  `user_id` int(11) NOT NULL,
  `preference_name` varchar(50) NOT NULL,
  `preference_value` text NOT NULL,
  PRIMARY KEY (`user_id`, `preference_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Insert default configuration values
INSERT INTO `nuke_claude_config` (`config_name`, `config_value`) VALUES
('api_key', ''),
('model', 'claude-3-sonnet-20240229'),
('max_tokens', '1000'),
('temperature', '0.7'),
('rate_limit_per_hour', '32'),
('max_context_messages', '10'),
('enable_logging', '1'),
('enable_moderation', '1'),
('welcome_message', 'Hello! I\'m Claude, an AI assistant. How can I help you today?');

INSERT INTO `nuke_modules` (`title`, `custom_title`, `active`, `view`, `inmenu`, `pos`, `cat_id`, `blocks`, `admins`, `groups`) VALUES
('Claude', 'Claude AI Chat', 1, 0, 1, 1, 7, 1, '', '');

-- Create indexes for better performance
ALTER TABLE `nuke_claude_chat` ADD INDEX `user_timestamp` (`user_id`, `timestamp`);
ALTER TABLE `nuke_claude_logs` ADD INDEX `user_timestamp` (`user_id`, `timestamp`);


