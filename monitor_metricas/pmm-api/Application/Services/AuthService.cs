using ProjectMetricsMonitor.Application.DTOs.Auth;
using ProjectMetricsMonitor.Application.Exceptions;
using ProjectMetricsMonitor.Application.Interfaces;
using ProjectMetricsMonitor.Domain.Entities;

namespace ProjectMetricsMonitor.Application.Services;

public sealed class AuthService : IAuthService
{
    private readonly IUserRepository _users;
    private readonly ISupabaseAuthService _supabase;
    private readonly IJwtTokenService _jwt;

    public AuthService(IUserRepository users, ISupabaseAuthService supabase, IJwtTokenService jwt)
    {
        _users = users;
        _supabase = supabase;
        _jwt = jwt;
    }

    public async Task<AuthResponse> RegisterAsync(AuthRegisterRequest request, CancellationToken cancellationToken)
    {
        var existing = await _users.GetByEmailAsync(request.Email, cancellationToken);
        if (existing is not null)
        {
            throw new HttpStatusCodeException(409, "Conflicto", "El correo ya está registrado.");
        }

        var supa = await _supabase.RegisterAsync(request.Email, request.Password, cancellationToken);

        var user = new User
        {
            Id = Guid.NewGuid(),
            SupabaseId = supa.SupabaseId,
            Email = supa.Email,
            FullName = string.IsNullOrWhiteSpace(request.FullName) ? (supa.FullName ?? supa.Email) : request.FullName.Trim(),
            Provider = string.IsNullOrWhiteSpace(supa.Provider) ? "email" : supa.Provider,
            CreatedAt = DateTimeOffset.UtcNow,
            UpdatedAt = DateTimeOffset.UtcNow,
        };

        await _users.AddAsync(user, cancellationToken);
        await _users.SaveChangesAsync(cancellationToken);

        return CreateAuthResponse(user);
    }

    public async Task<AuthResponse> LoginAsync(AuthLoginRequest request, CancellationToken cancellationToken)
    {
        var supa = await _supabase.LoginWithPasswordAsync(request.Email, request.Password, cancellationToken);

        var user = await _users.GetBySupabaseIdAsync(supa.SupabaseId, cancellationToken)
                   ?? await _users.GetByEmailAsync(supa.Email, cancellationToken);

        if (user is null)
        {
            user = new User
            {
                Id = Guid.NewGuid(),
                SupabaseId = supa.SupabaseId,
                Email = supa.Email,
                FullName = supa.FullName ?? supa.Email,
                Provider = string.IsNullOrWhiteSpace(supa.Provider) ? "email" : supa.Provider,
                CreatedAt = DateTimeOffset.UtcNow,
                UpdatedAt = DateTimeOffset.UtcNow,
            };

            await _users.AddAsync(user, cancellationToken);
        }
        else
        {
            user.SupabaseId = string.IsNullOrWhiteSpace(user.SupabaseId) ? supa.SupabaseId : user.SupabaseId;
            user.Email = supa.Email;
            if (!string.IsNullOrWhiteSpace(supa.FullName)) user.FullName = supa.FullName;
            user.Provider = string.IsNullOrWhiteSpace(supa.Provider) ? user.Provider : supa.Provider;
            user.UpdatedAt = DateTimeOffset.UtcNow;
        }

        await _users.SaveChangesAsync(cancellationToken);

        return CreateAuthResponse(user);
    }

    public async Task<AuthResponse> LoginWithGoogleAsync(GoogleLoginRequest request, CancellationToken cancellationToken)
    {
        var supa = await _supabase.GetUserAsync(request.SupabaseAccessToken, cancellationToken);

        var user = await _users.GetBySupabaseIdAsync(supa.SupabaseId, cancellationToken)
                   ?? await _users.GetByEmailAsync(supa.Email, cancellationToken);

        if (user is null)
        {
            user = new User
            {
                Id = Guid.NewGuid(),
                SupabaseId = supa.SupabaseId,
                Email = supa.Email,
                FullName = supa.FullName ?? supa.Email,
                Provider = string.IsNullOrWhiteSpace(supa.Provider) ? "google" : supa.Provider,
                CreatedAt = DateTimeOffset.UtcNow,
                UpdatedAt = DateTimeOffset.UtcNow,
            };

            await _users.AddAsync(user, cancellationToken);
        }
        else
        {
            user.SupabaseId = string.IsNullOrWhiteSpace(user.SupabaseId) ? supa.SupabaseId : user.SupabaseId;
            user.Email = supa.Email;
            if (!string.IsNullOrWhiteSpace(supa.FullName)) user.FullName = supa.FullName;
            user.Provider = string.IsNullOrWhiteSpace(supa.Provider) ? user.Provider : supa.Provider;
            user.UpdatedAt = DateTimeOffset.UtcNow;
        }

        await _users.SaveChangesAsync(cancellationToken);

        return CreateAuthResponse(user);
    }

    public Task ForgotPasswordAsync(ForgotPasswordRequest request, CancellationToken cancellationToken)
    {
        return _supabase.SendPasswordResetAsync(request.Email, cancellationToken);
    }

    private AuthResponse CreateAuthResponse(User user)
    {
        var (token, expiresAt) = _jwt.CreateToken(user);
        return new AuthResponse(
            token,
            new UserDto(user.Id, user.FullName, user.Email),
            expiresAt
        );
    }
}
