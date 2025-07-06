
import os

# Bot Configuration
BOT_PREFIX = '!'
BOT_TOKEN = os.getenv('DISCORD_BOT_TOKEN')

# Database Configuration
DATABASE_PATH = 'roblox_bot.db'

# Roblox API Configuration
ROBLOX_API_BASE = 'https://groups.roblox.com/v1/groups/'
ROBLOX_USERS_API = 'https://users.roblox.com/v1/users/'

# Bot Settings
MAX_EMBED_FIELDS = 25
MAX_MEMBERS_DISPLAY = 10
MAX_GROUPS_DISPLAY = 10

# Permission Types
VALID_PERMISSIONS = [
    'manage_ranks',
    'edit_group_name', 
    'kick_members',
    'invite_members',
    'ban_members'
]

# Permission Names (Turkish)
PERMISSION_NAMES = {
    'manage_ranks': 'Rütbe Yönetimi',
    'edit_group_name': 'Grup Adı Düzenleme',
    'kick_members': 'Üye Atma',
    'invite_members': 'Üye Davet Etme',
    'ban_members': 'Üye Banlama'
}

# Embed Colors
COLORS = {
    'success': 0x00ff00,
    'error': 0xff0000,
    'info': 0x0099ff,
    'warning': 0xffaa00,
    'purple': 0x9932cc
}
