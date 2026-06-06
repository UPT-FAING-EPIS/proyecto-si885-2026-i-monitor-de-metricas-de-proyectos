using ProjectMetricsMonitor.Application.DTOs.Auth;

namespace ProjectMetricsMonitor.Application.Interfaces;

public interface IAuthService
{
    Task<AuthResponse> RegisterAsync(AuthRegisterRequest request, CancellationToken cancellationToken);
    Task<AuthResponse> LoginAsync(AuthLoginRequest request, CancellationToken cancellationToken);
    Task<AuthResponse> LoginWithGoogleAsync(GoogleLoginRequest request, CancellationToken cancellationToken);
    Task ForgotPasswordAsync(ForgotPasswordRequest request, CancellationToken cancellationToken);
}

