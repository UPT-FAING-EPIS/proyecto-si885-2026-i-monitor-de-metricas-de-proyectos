using FluentValidation;
using ProjectMetricsMonitor.Application.DTOs.Auth;

namespace ProjectMetricsMonitor.Application.Validators.Auth;

public sealed class AuthRegisterRequestValidator : AbstractValidator<AuthRegisterRequest>
{
    public AuthRegisterRequestValidator()
    {
        RuleFor(x => x.FullName).NotEmpty();
        RuleFor(x => x.Email).NotEmpty().EmailAddress();
        RuleFor(x => x.Password).NotEmpty().MinimumLength(8);
    }
}

