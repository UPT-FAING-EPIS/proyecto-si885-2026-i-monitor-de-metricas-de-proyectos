using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using ProjectMetricsMonitor.Application.DTOs.Auth;
using ProjectMetricsMonitor.Application.Interfaces;

namespace ProjectMetricsMonitor.API.Controllers;

[ApiController]
[Route("api/auth")]
public sealed class AuthController : ControllerBase
{
    private readonly IAuthService _auth;

    public AuthController(IAuthService auth)
    {
        _auth = auth;
    }

    [HttpPost("register")]
    [ProducesResponseType(typeof(AuthResponse), StatusCodes.Status200OK)]
    public Task<AuthResponse> Register([FromBody] AuthRegisterRequest request, CancellationToken cancellationToken)
    {
        return _auth.RegisterAsync(request, cancellationToken);
    }

    [HttpPost("login")]
    [ProducesResponseType(typeof(AuthResponse), StatusCodes.Status200OK)]
    public Task<AuthResponse> Login([FromBody] AuthLoginRequest request, CancellationToken cancellationToken)
    {
        return _auth.LoginAsync(request, cancellationToken);
    }

    [HttpPost("google")]
    [ProducesResponseType(typeof(AuthResponse), StatusCodes.Status200OK)]
    public Task<AuthResponse> Google([FromBody] GoogleLoginRequest request, CancellationToken cancellationToken)
    {
        return _auth.LoginWithGoogleAsync(request, cancellationToken);
    }

    [HttpPost("forgot-password")]
    [ProducesResponseType(StatusCodes.Status204NoContent)]
    public async Task<IActionResult> ForgotPassword([FromBody] ForgotPasswordRequest request, CancellationToken cancellationToken)
    {
        await _auth.ForgotPasswordAsync(request, cancellationToken);
        return NoContent();
    }

    [Authorize]
    [HttpPost("logout")]
    [ProducesResponseType(StatusCodes.Status204NoContent)]
    public IActionResult Logout()
    {
        return NoContent();
    }
}

