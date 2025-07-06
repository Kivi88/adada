
import discord
from discord.ext import commands
import asyncio
import aiohttp
import json
import os
from datetime import datetime
import sqlite3

# Bot setup
intents = discord.Intents.default()
intents.message_content = True
bot = commands.Bot(command_prefix='!', intents=intents)

# Database setup
def init_db():
    conn = sqlite3.connect('roblox_bot.db')
    cursor = conn.cursor()
    
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS users (
            discord_id TEXT PRIMARY KEY,
            username TEXT,
            role TEXT DEFAULT 'user',
            group_id INTEGER,
            group_name TEXT,
            roblox_cookie TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS helpers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            discord_id TEXT,
            group_id INTEGER,
            username TEXT,
            permissions TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (discord_id) REFERENCES users(discord_id)
        )
    ''')
    
    conn.commit()
    conn.close()

class RobloxAPI:
    def __init__(self, cookie=None):
        self.cookie = cookie
        self.session = None
    
    async def get_session(self):
        if not self.session:
            self.session = aiohttp.ClientSession()
        return self.session
    
    async def get_group_info(self, group_id):
        session = await self.get_session()
        try:
            async with session.get(f'https://groups.roblox.com/v1/groups/{group_id}') as resp:
                if resp.status == 200:
                    return await resp.json()
        except:
            pass
        return None
    
    async def get_group_members(self, group_id, limit=100):
        session = await self.get_session()
        try:
            async with session.get(f'https://groups.roblox.com/v1/groups/{group_id}/users?limit={limit}') as resp:
                if resp.status == 200:
                    return await resp.json()
        except:
            pass
        return None
    
    async def get_user_by_username(self, username):
        session = await self.get_session()
        try:
            data = {"usernames": [username]}
            async with session.post('https://users.roblox.com/v1/usernames/users', 
                                  json=data) as resp:
                if resp.status == 200:
                    result = await resp.json()
                    return result.get('data', [{}])[0] if result.get('data') else None
        except:
            pass
        return None
    
    async def get_user_groups(self, user_id):
        session = await self.get_session()
        try:
            async with session.get(f'https://users.roblox.com/v1/users/{user_id}/groups/roles') as resp:
                if resp.status == 200:
                    return await resp.json()
        except:
            pass
        return None

# Helper functions
def get_user_data(discord_id):
    conn = sqlite3.connect('roblox_bot.db')
    cursor = conn.cursor()
    cursor.execute('SELECT * FROM users WHERE discord_id = ?', (str(discord_id),))
    user = cursor.fetchone()
    conn.close()
    return user

def is_admin(discord_id):
    user = get_user_data(discord_id)
    return user and user[2] == 'admin'

def is_owner(discord_id):
    user = get_user_data(discord_id)
    return user and user[2] == 'owner'

@bot.event
async def on_ready():
    print(f'{bot.user} Discord botuna bağlandı!')
    init_db()

@bot.command(name='grup')
async def group_lookup(ctx, group_id: int):
    """Grup bilgilerini getir"""
    api = RobloxAPI()
    
    embed = discord.Embed(title="🔍 Grup Aranıyor...", color=0x00ff00)
    message = await ctx.send(embed=embed)
    
    group_info = await api.get_group_info(group_id)
    
    if not group_info:
        embed = discord.Embed(
            title="❌ Hata", 
            description="Grup bulunamadı veya API erişimi yok",
            color=0xff0000
        )
        await message.edit(embed=embed)
        return
    
    group_members = await api.get_group_members(group_id)
    member_count = len(group_members.get('data', [])) if group_members else 0
    
    embed = discord.Embed(
        title=f"📋 {group_info['name']}", 
        description=group_info.get('description', 'Açıklama yok')[:2000],
        color=0x0099ff
    )
    embed.add_field(name="🆔 Grup ID", value=group_info['id'], inline=True)
    embed.add_field(name="👥 Üye Sayısı", value=f"{group_info['memberCount']:,}", inline=True)
    embed.add_field(name="🔓 Herkese Açık", value="Evet" if group_info.get('publicEntryAllowed') else "Hayır", inline=True)
    embed.add_field(name="📊 Yüklenen Üyeler", value=member_count, inline=True)
    embed.set_footer(text=f"Tarih: {datetime.now().strftime('%d/%m/%Y %H:%M')}")
    
    await message.edit(embed=embed)
    
    if group_members and group_members.get('data'):
        members_text = ""
        for i, member in enumerate(group_members['data'][:10]):  # İlk 10 üye
            members_text += f"{i+1}. **{member['user']['username']}** - {member['role']['name']}\n"
        
        if len(group_members['data']) > 10:
            members_text += f"... ve {len(group_members['data']) - 10} üye daha"
        
        embed2 = discord.Embed(
            title="👥 Grup Üyeleri (İlk 10)",
            description=members_text,
            color=0x0099ff
        )
        await ctx.send(embed=embed2)

@bot.command(name='oyuncu')
async def player_search(ctx, *, username):
    """Oyuncu bilgilerini ara"""
    api = RobloxAPI()
    
    embed = discord.Embed(title="🔍 Oyuncu Aranıyor...", color=0x00ff00)
    message = await ctx.send(embed=embed)
    
    user_data = await api.get_user_by_username(username)
    
    if not user_data:
        embed = discord.Embed(
            title="❌ Hata", 
            description="Kullanıcı bulunamadı veya API erişimi yok",
            color=0xff0000
        )
        await message.edit(embed=embed)
        return
    
    user_groups = await api.get_user_groups(user_data['id'])
    
    embed = discord.Embed(
        title=f"👤 {user_data['name']}", 
        description=user_data.get('description', 'Açıklama yok')[:2000],
        color=0x9932cc
    )
    embed.add_field(name="🆔 Kullanıcı ID", value=user_data['id'], inline=True)
    embed.add_field(name="📅 Hesap Oluşturma", value=user_data.get('created', 'Bilinmiyor')[:10], inline=True)
    embed.set_footer(text=f"Tarih: {datetime.now().strftime('%d/%m/%Y %H:%M')}")
    
    await message.edit(embed=embed)
    
    if user_groups and user_groups.get('data'):
        groups_text = ""
        for i, group in enumerate(user_groups['data'][:10]):  # İlk 10 grup
            groups_text += f"{i+1}. **{group['group']['name']}** - {group['role']['name']}\n"
        
        if len(user_groups['data']) > 10:
            groups_text += f"... ve {len(user_groups['data']) - 10} grup daha"
        
        embed2 = discord.Embed(
            title="🏰 Kullanıcının Grupları (İlk 10)",
            description=groups_text,
            color=0x9932cc
        )
        await ctx.send(embed=embed2)

@bot.command(name='kayıt')
async def register_owner(ctx, group_id: int, *, group_name):
    """Grup sahibi olarak kayıt ol (Sadece adminler kullanabilir)"""
    if not is_admin(ctx.author.id):
        await ctx.send("❌ Bu komutu sadece adminler kullanabilir!")
        return
    
    conn = sqlite3.connect('roblox_bot.db')
    cursor = conn.cursor()
    
    try:
        cursor.execute('''
            INSERT OR REPLACE INTO users 
            (discord_id, username, role, group_id, group_name) 
            VALUES (?, ?, ?, ?, ?)
        ''', (str(ctx.author.id), str(ctx.author), 'owner', group_id, group_name))
        conn.commit()
        
        embed = discord.Embed(
            title="✅ Başarılı!",
            description=f"**{group_name}** ({group_id}) grubu için kayıt oldunuz!",
            color=0x00ff00
        )
        await ctx.send(embed=embed)
        
    except Exception as e:
        embed = discord.Embed(
            title="❌ Hata",
            description=f"Kayıt sırasında hata: {str(e)}",
            color=0xff0000
        )
        await ctx.send(embed=embed)
    finally:
        conn.close()

@bot.command(name='admin_ekle')
async def add_admin(ctx, user: discord.Member):
    """Kullanıcıyı admin yap (Sadece mevcut adminler)"""
    if not is_admin(ctx.author.id):
        await ctx.send("❌ Bu komutu sadece adminler kullanabilir!")
        return
    
    conn = sqlite3.connect('roblox_bot.db')
    cursor = conn.cursor()
    
    try:
        cursor.execute('''
            INSERT OR REPLACE INTO users 
            (discord_id, username, role) 
            VALUES (?, ?, ?)
        ''', (str(user.id), str(user), 'admin'))
        conn.commit()
        
        embed = discord.Embed(
            title="✅ Admin Eklendi!",
            description=f"**{user.mention}** artık admin!",
            color=0x00ff00
        )
        await ctx.send(embed=embed)
        
    except Exception as e:
        embed = discord.Embed(
            title="❌ Hata",
            description=f"Admin ekleme hatası: {str(e)}",
            color=0xff0000
        )
        await ctx.send(embed=embed)
    finally:
        conn.close()

@bot.command(name='panel')
async def dashboard(ctx):
    """Kullanıcı paneli"""
    user = get_user_data(ctx.author.id)
    
    if not user:
        embed = discord.Embed(
            title="❌ Erişim Reddedildi",
            description="Sisteme kayıtlı değilsiniz. Admin ile iletişime geçin.",
            color=0xff0000
        )
        await ctx.send(embed=embed)
        return
    
    embed = discord.Embed(
        title="📊 Kullanıcı Paneli",
        description=f"Hoş geldiniz, **{ctx.author.mention}**!",
        color=0x0099ff
    )
    embed.add_field(name="👤 Kullanıcı Adı", value=user[1], inline=True)
    embed.add_field(name="🎭 Rol", value=user[2].title(), inline=True)
    
    if user[3]:  # group_id
        embed.add_field(name="🏰 Grup ID", value=user[3], inline=True)
        embed.add_field(name="📝 Grup Adı", value=user[4] or "Bilinmiyor", inline=True)
    
    embed.add_field(name="📅 Kayıt Tarihi", value=user[6][:10] if user[6] else "Bilinmiyor", inline=True)
    embed.set_footer(text="Komutlar için !yardım yazın")
    
    await ctx.send(embed=embed)

@bot.command(name='yardımcı_ekle')
async def add_helper(ctx, user: discord.Member, *permissions):
    """Yardımcı ekle (Sadece grup sahipleri)"""
    if not is_owner(ctx.author.id):
        await ctx.send("❌ Bu komutu sadece grup sahipleri kullanabilir!")
        return
    
    owner_data = get_user_data(ctx.author.id)
    if not owner_data or not owner_data[3]:
        await ctx.send("❌ Grup bilginiz bulunamadı!")
        return
    
    valid_permissions = ['manage_ranks', 'edit_group_name', 'kick_members', 'invite_members', 'ban_members']
    filtered_perms = [p for p in permissions if p in valid_permissions]
    
    if not filtered_perms:
        await ctx.send(f"❌ Geçerli izinler: {', '.join(valid_permissions)}")
        return
    
    conn = sqlite3.connect('roblox_bot.db')
    cursor = conn.cursor()
    
    try:
        cursor.execute('''
            INSERT OR REPLACE INTO helpers 
            (discord_id, group_id, username, permissions) 
            VALUES (?, ?, ?, ?)
        ''', (str(user.id), owner_data[3], str(user), ','.join(filtered_perms)))
        conn.commit()
        
        embed = discord.Embed(
            title="✅ Yardımcı Eklendi!",
            description=f"**{user.mention}** yardımcı olarak eklendi!",
            color=0x00ff00
        )
        embed.add_field(name="🔑 İzinler", value='\n'.join(filtered_perms), inline=False)
        await ctx.send(embed=embed)
        
    except Exception as e:
        embed = discord.Embed(
            title="❌ Hata",
            description=f"Yardımcı ekleme hatası: {str(e)}",
            color=0xff0000
        )
        await ctx.send(embed=embed)
    finally:
        conn.close()

@bot.command(name='yardımcılar')
async def list_helpers(ctx):
    """Yardımcıları listele (Sadece grup sahipleri)"""
    if not is_owner(ctx.author.id):
        await ctx.send("❌ Bu komutu sadece grup sahipleri kullanabilir!")
        return
    
    owner_data = get_user_data(ctx.author.id)
    if not owner_data or not owner_data[3]:
        await ctx.send("❌ Grup bilginiz bulunamadı!")
        return
    
    conn = sqlite3.connect('roblox_bot.db')
    cursor = conn.cursor()
    cursor.execute('SELECT * FROM helpers WHERE group_id = ?', (owner_data[3],))
    helpers = cursor.fetchall()
    conn.close()
    
    if not helpers:
        await ctx.send("📝 Henüz yardımcı eklenmemiş.")
        return
    
    embed = discord.Embed(
        title="👥 Grup Yardımcıları",
        description=f"**{owner_data[4]}** grubu yardımcıları",
        color=0x0099ff
    )
    
    for helper in helpers:
        permissions = helper[4].split(',') if helper[4] else []
        embed.add_field(
            name=f"👤 {helper[3]}",
            value=f"🔑 **İzinler:** {', '.join(permissions)}\n📅 **Ekleme:** {helper[5][:10]}",
            inline=False
        )
    
    await ctx.send(embed=embed)

@bot.command(name='istatistik')
async def stats(ctx):
    """Genel istatistikler (Sadece adminler)"""
    if not is_admin(ctx.author.id):
        await ctx.send("❌ Bu komutu sadece adminler kullanabilir!")
        return
    
    conn = sqlite3.connect('roblox_bot.db')
    cursor = conn.cursor()
    
    cursor.execute('SELECT COUNT(*) FROM users WHERE role = "owner"')
    owner_count = cursor.fetchone()[0]
    
    cursor.execute('SELECT COUNT(*) FROM users WHERE role = "admin"')
    admin_count = cursor.fetchone()[0]
    
    cursor.execute('SELECT COUNT(*) FROM helpers')
    helper_count = cursor.fetchone()[0]
    
    cursor.execute('SELECT COUNT(DISTINCT group_id) FROM users WHERE group_id IS NOT NULL')
    group_count = cursor.fetchone()[0]
    
    conn.close()
    
    embed = discord.Embed(
        title="📊 Sistem İstatistikleri",
        color=0x0099ff
    )
    embed.add_field(name="👑 Adminler", value=admin_count, inline=True)
    embed.add_field(name="👤 Grup Sahipleri", value=owner_count, inline=True)
    embed.add_field(name="🤝 Yardımcılar", value=helper_count, inline=True)
    embed.add_field(name="🏰 Gruplar", value=group_count, inline=True)
    embed.set_footer(text=f"Bot Aktif: {datetime.now().strftime('%d/%m/%Y %H:%M')}")
    
    await ctx.send(embed=embed)

@bot.command(name='yardım')
async def help_command(ctx):
    """Yardım menüsü"""
    embed = discord.Embed(
        title="🤖 Roblox Grup Yönetim Botu",
        description="Kullanılabilir komutlar:",
        color=0x0099ff
    )
    
    embed.add_field(
        name="🔍 Arama Komutları",
        value="`!grup <id>` - Grup bilgisi\n`!oyuncu <username>` - Oyuncu bilgisi",
        inline=False
    )
    
    embed.add_field(
        name="👤 Kullanıcı Komutları",
        value="`!panel` - Kullanıcı paneli\n`!yardım` - Bu menü",
        inline=False
    )
    
    user = get_user_data(ctx.author.id)
    if user and user[2] == 'owner':
        embed.add_field(
            name="👑 Grup Sahibi Komutları",
            value="`!yardımcı_ekle <@user> <izinler>` - Yardımcı ekle\n`!yardımcılar` - Yardımcıları listele",
            inline=False
        )
    
    if user and user[2] == 'admin':
        embed.add_field(
            name="⚙️ Admin Komutları",
            value="`!kayıt <grup_id> <grup_adı>` - Grup sahibi kayıt\n`!admin_ekle <@user>` - Admin ekle\n`!istatistik` - Sistem istatistikleri",
            inline=False
        )
    
    embed.set_footer(text="Roblox Grup Yönetim Sistemi - Discord Bot")
    await ctx.send(embed=embed)

# Error handling
@bot.event
async def on_command_error(ctx, error):
    if isinstance(error, commands.CommandNotFound):
        await ctx.send("❌ Geçersiz komut! `!yardım` yazarak komutları görebilirsiniz.")
    elif isinstance(error, commands.MissingRequiredArgument):
        await ctx.send("❌ Eksik parametre! Komut kullanımını kontrol edin.")
    else:
        await ctx.send(f"❌ Bir hata oluştu: {str(error)}")

if __name__ == "__main__":
    # Bot token'ı çevre değişkeninden al
    TOKEN = os.getenv('DISCORD_BOT_TOKEN')
    if not TOKEN:
        print("❌ DISCORD_BOT_TOKEN çevre değişkeni bulunamadı!")
    else:
        bot.run(TOKEN)
