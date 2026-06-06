using ProjectMetricsMonitor.Application.Models;

namespace ProjectMetricsMonitor.Application.Interfaces;

public interface ISupabaseAuthService
{
    Task<SupabaseUserInfo> RegisterAsync(string email, string password, CancellationToken cancellationToken);
    Task<SupabaseUserInfo> LoginWithPasswordAsync(string email, string password, CancellationToken cancellationToken);
    Task<SupabaseUserInfo> GetUserAsync(string supabaseAccessToken, CancellationToken cancellationToken);
    Task SendPasswordResetAsync(string email, CancellationToken cancellationToken);
}

