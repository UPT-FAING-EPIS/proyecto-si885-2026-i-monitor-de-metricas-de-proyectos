using Microsoft.EntityFrameworkCore;
using ProjectMetricsMonitor.Application.Interfaces;
using ProjectMetricsMonitor.Domain.Entities;

namespace ProjectMetricsMonitor.Persistence.Repositories;

public sealed class UserRepository : IUserRepository
{
    private readonly AppDbContext _db;

    public UserRepository(AppDbContext db)
    {
        _db = db;
    }

    public Task<User?> GetByEmailAsync(string email, CancellationToken cancellationToken)
    {
        var normalized = email.Trim().ToLowerInvariant();
        return _db.Users.FirstOrDefaultAsync(x => x.Email.ToLower() == normalized, cancellationToken);
    }

    public Task<User?> GetBySupabaseIdAsync(string supabaseId, CancellationToken cancellationToken)
    {
        return _db.Users.FirstOrDefaultAsync(x => x.SupabaseId == supabaseId, cancellationToken);
    }

    public Task AddAsync(User user, CancellationToken cancellationToken)
    {
        return _db.Users.AddAsync(user, cancellationToken).AsTask();
    }

    public Task SaveChangesAsync(CancellationToken cancellationToken)
    {
        return _db.SaveChangesAsync(cancellationToken);
    }
}
