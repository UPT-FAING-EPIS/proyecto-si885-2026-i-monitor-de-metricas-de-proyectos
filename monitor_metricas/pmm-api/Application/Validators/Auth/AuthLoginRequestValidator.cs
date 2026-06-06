using FluentValidation;
using ProjectMetricsMonitor.Application.DTOs.Auth;

namespace ProjectMetricsMonitor.Application.Validators.Auth;

public sealed class AuthLoginRequestValidator : AbstractValidator<AuthLoginRequest>
{
    public AuthLoginRequestValidator()
    {
        RuleFor(x => x.Email).NotEmpty().EmailAddress();
        RuleFor(x => x.Password).NotEmpty().MinimumLength(8);
    }
}

