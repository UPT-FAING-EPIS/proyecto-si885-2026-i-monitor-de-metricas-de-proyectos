using FluentValidation;
using ProjectMetricsMonitor.Application.DTOs.Auth;

namespace ProjectMetricsMonitor.Application.Validators.Auth;

public sealed class GoogleLoginRequestValidator : AbstractValidator<GoogleLoginRequest>
{
    public GoogleLoginRequestValidator()
    {
        RuleFor(x => x.SupabaseAccessToken).NotEmpty();
    }
}

